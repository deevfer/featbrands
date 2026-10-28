<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ContactSubmissions extends Page
{
    protected static ?string $title = 'Solicitudes de contacto';

    protected static ?string $navigationLabel = 'Contacto';

    protected string $view = 'filament.pages.contact-submissions';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'submissions' => DB::table('contact_submissions')->orderByDesc('id')->paginate(15),
            'referrals' => File::json(resource_path('data/contact.json'))['es']['referrals'],
        ];
    }
}
