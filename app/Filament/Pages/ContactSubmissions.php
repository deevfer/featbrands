<?php

namespace App\Filament\Pages;

use App\Services\SiteContent;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class ContactSubmissions extends Page
{
    protected static ?string $title = 'Solicitudes de contacto';

    protected static ?string $navigationLabel = 'Solicitudes de contacto';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected string $view = 'filament.pages.contact-submissions';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'submissions' => DB::table('contact_submissions')->orderByDesc('id')->paginate(15),
            'referrals' => app(SiteContent::class)->get('contact', 'es')['referrals'],
        ];
    }
}
