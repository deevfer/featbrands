<?php

namespace App\Filament\Widgets;

use App\Models\SeoAudit;
use Filament\Widgets\Widget;

class SeoOverview extends Widget
{
    protected string $view = 'filament.widgets.seo-overview';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -1;

    protected static bool $isLazy = false;

    protected function getViewData(): array
    {
        return ['audit' => SeoAudit::latest('id')->first(['score', 'page_count', 'created_at'])];
    }
}
