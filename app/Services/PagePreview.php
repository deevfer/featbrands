<?php

namespace App\Services;

use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Renders a public page with unsaved CMS changes: the draft is written inside a transaction
 * that is always rolled back, so nothing reaches the site until the editor saves.
 */
class PagePreview
{
    public function __construct(private Router $router) {}

    /**
     * @param  Closure(): string  $applyDraft  Writes the draft exactly as saving would and returns the path to render.
     * @return array{html: string|null, status: int}
     */
    public function render(Closure $applyDraft): array
    {
        DB::beginTransaction();

        try {
            return $this->fetch($applyDraft());
        } finally {
            DB::rollBack();
        }
    }

    /**
     * Runs the public route in-process, without middleware, so the editor's session is untouched.
     *
     * @return array{html: string|null, status: int}
     */
    private function fetch(string $path): array
    {
        $original = app('request');
        $errors = View::shared('errors');
        $request = Request::create(url($path));
        $request->setLaravelSession(app('session.store'));
        View::share('errors', new ViewErrorBag);

        try {
            $route = $this->router->getRoutes()->match($request);
            $request->setRouteResolver(fn () => $route);
            $this->useRequest($request);
            $response = Router::toResponse($request, $route->run());

            return $response->getStatusCode() === 200
                ? ['html' => (string) $response->getContent(), 'status' => 200]
                : ['html' => null, 'status' => $response->getStatusCode()];
        } catch (HttpException $exception) {
            return ['html' => null, 'status' => $exception->getStatusCode()];
        } catch (ModelNotFoundException) {
            return ['html' => null, 'status' => 404];
        } catch (Throwable $exception) {
            report($exception);

            return ['html' => null, 'status' => 500];
        } finally {
            $this->useRequest($original);
            View::share('errors', $errors ?? new ViewErrorBag);
        }
    }

    private function useRequest(Request $request): void
    {
        app()->instance('request', $request);
        Facade::clearResolvedInstance('request');
    }
}
