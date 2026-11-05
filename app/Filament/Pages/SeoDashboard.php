<?php

namespace App\Filament\Pages;

use App\Models\SeoAudit;
use App\Services\SeoAuditor;
use App\Services\SeoPublicVerifier;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;
use UnitEnum;

class SeoDashboard extends Page
{
    use WithPagination;

    protected static ?string $title = 'SEO y búsquedas con IA';

    protected static ?string $navigationLabel = 'Dashboard SEO';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Visibilidad';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.seo-dashboard';

    public string $search = '';

    public bool $onlyIssues = false;

    public function analyze(): void
    {
        $audit = app(SeoAuditor::class)->run();
        $this->resetPage();
        Notification::make()->title('Análisis actualizado: '.$audit->score.'/100')->success()->send();
    }

    public function verifyPublication(): void
    {
        $result = app(SeoPublicVerifier::class)->verify();
        app(SeoAuditor::class)->run();
        $this->resetPage();
        Notification::make()->title($result['passed'] ? 'Publicación verificada' : 'Hay comprobaciones pendientes en el dominio público')->color($result['passed'] ? 'success' : 'warning')->send();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedOnlyIssues(): void
    {
        $this->resetPage();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('analyze')->label('Analizar ahora')->icon(Heroicon::OutlinedArrowPath)->action(fn () => $this->analyze()),
            Action::make('verify')->label('Verificar publicación')->color('gray')->action(fn () => $this->verifyPublication()),
            Action::make('settings')->label('Configuración')->color('gray')->url(SeoSettings::getUrl()),
        ];
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $audit = SeoAudit::latest('id')->first();
        $report = $audit?->report;
        $items = collect($report['pages'] ?? [])->filter(fn (array $page): bool => (! $this->onlyIssues || $page['score'] < 100) && ($this->search === '' || mb_stripos($page['title'].' '.$page['path'].' '.$page['locale'], $this->search) !== false))->values();
        $pages = new LengthAwarePaginator($items->forPage($this->getPage(), 15)->values(), $items->count(), 15, $this->getPage());

        return ['audit' => $audit, 'report' => $report, 'pages' => $pages, 'history' => SeoAudit::latest('id')->limit(8)->get(['id', 'score', 'page_count', 'created_at']), 'sources' => config('seo.sources')];
    }
}
