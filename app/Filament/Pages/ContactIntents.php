<?php

namespace App\Filament\Pages;

use App\Models\ContactIntent;
use App\Services\SiteContent;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Contact forms that visitors started and did not send, with the ad cookies they allowed, for follow-up and retargeting.
 */
class ContactIntents extends Page
{
    protected static ?string $title = 'Formularios sin completar';

    protected static ?string $navigationLabel = 'Formularios sin completar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected string $view = 'filament.pages.contact-intents';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'intents' => ContactIntent::query()->whereNull('submitted_at')->latest('last_activity_at')->paginate(15),
            'referrals' => app(SiteContent::class)->get('contact', 'es')['referrals'],
            'waitMinutes' => ContactIntent::ABANDONED_AFTER_MINUTES,
        ];
    }
}
