<?php

namespace App\Providers;

use App\Services\SiteIcons;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(SiteIcons::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FileUpload::configureUsing(function (FileUpload $upload): void {
            $upload->getUploadedFileUsing(static function (FileUpload $component, string $file, string|array|null $storedFileNames): ?array {
                $uploaded = $component->getUploadedFile($file, $storedFileNames);

                if (filled($uploaded['url'] ?? null)) {
                    $uploaded['url'] = self::withoutAppOrigin($uploaded['url']);
                }

                return $uploaded;
            });
        });
    }

    /**
     * The panel loads existing uploads with fetch(), which the browser blocks when
     * APP_URL's host differs from the one in the address bar (localhost vs 127.0.0.1,
     * www vs no www); a relative URL always targets the host the panel is open on.
     */
    private static function withoutAppOrigin(string $url): string
    {
        $appUrl = parse_url((string) config('app.url'));
        $origin = ($appUrl['scheme'] ?? 'http').'://'.($appUrl['host'] ?? '').(isset($appUrl['port']) ? ':'.$appUrl['port'] : '');

        return str_starts_with($url, $origin.'/') ? substr($url, strlen($origin)) : $url;
    }
}
