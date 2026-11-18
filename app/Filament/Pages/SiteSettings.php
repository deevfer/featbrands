<?php

namespace App\Filament\Pages;

use App\Mail\SettingsTestMail;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use Throwable;
use UnitEnum;

/**
 * Outgoing mail, reCAPTCHA and measurement tags of the public site.
 */
class SiteSettings extends Page
{
    protected static ?string $navigationLabel = 'Configuración del sitio';

    protected static ?string $title = 'Configuración del sitio';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.site-settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::current();

        $this->form->fill([
            ...$settings->attributesToArray(),
            'mail_password' => null,
            'recaptcha_secret_key' => null,
            'mail_to_address' => $settings->mailRecipients(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Tabs::make('Configuración del sitio')->persistTabInQueryString()->tabs([
                        Tab::make('Correos')->icon(Heroicon::OutlinedEnvelope)->schema([
                            Section::make('Envío')
                                ->description('Cuenta con la que el sitio envía el aviso de cada nuevo contacto al equipo y la confirmación al cliente.')
                                ->columns(2)
                                ->schema([
                                    Toggle::make('mail_enabled')->label('Enviar correos')->helperText('Apagado, las solicitudes se guardan en el CMS pero no se envía ningún correo.')->columnSpanFull(),
                                    Select::make('mail_mailer')->label('Método')->options(['smtp' => 'SMTP', 'sendmail' => 'Sendmail del servidor', 'log' => 'Solo registrar (pruebas)'])->required()->live(),
                                    Select::make('mail_encryption')->label('Cifrado')->options(['tls' => 'TLS (puerto 587)', 'ssl' => 'SSL (puerto 465)', 'none' => 'Ninguno'])->required()->visible(fn (Get $get): bool => $get('mail_mailer') === 'smtp'),
                                    TextInput::make('mail_host')->label('Servidor SMTP')->placeholder('mail.featbrands.com')->maxLength(255)->required(fn (Get $get): bool => $get('mail_enabled') && $get('mail_mailer') === 'smtp')->visible(fn (Get $get): bool => $get('mail_mailer') === 'smtp'),
                                    TextInput::make('mail_port')->label('Puerto')->numeric()->minValue(1)->maxValue(65535)->required(fn (Get $get): bool => $get('mail_enabled') && $get('mail_mailer') === 'smtp')->visible(fn (Get $get): bool => $get('mail_mailer') === 'smtp'),
                                    TextInput::make('mail_username')->label('Usuario')->maxLength(255)->autocomplete('off')->visible(fn (Get $get): bool => $get('mail_mailer') === 'smtp'),
                                    TextInput::make('mail_password')->label('Contraseña')->password()->revealable()->autocomplete('new-password')->maxLength(255)
                                        ->placeholder(fn (): string => filled(SiteSetting::current()->mail_password) ? '•••••••• (guardada)' : '')
                                        ->helperText('Vacío conserva la contraseña guardada.')
                                        ->visible(fn (Get $get): bool => $get('mail_mailer') === 'smtp'),
                                ]),
                            Section::make('Remitente y destinatarios')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('mail_from_address')->label('Correo remitente')->email()->maxLength(255)->required(fn (Get $get): bool => (bool) $get('mail_enabled')),
                                    TextInput::make('mail_from_name')->label('Nombre del remitente')->maxLength(255)->placeholder('Feat.'),
                                    TagsInput::make('mail_to_address')->label('Correos del equipo que reciben cada contacto')->placeholder('nombre@featbrands.com')
                                        ->nestedRecursiveRules(['email'])->helperText('Escribe un correo y presiona Enter. El primero también es la dirección de respuesta para los clientes.')->columnSpanFull(),
                                ]),
                        ]),
                        Tab::make('Seguridad')->icon(Heroicon::OutlinedShieldCheck)->schema([
                            Section::make('Google reCAPTCHA v3')
                                ->description('Protege el formulario de contacto contra spam sin pedir nada a la persona. Crea las claves de tipo v3 en google.com/recaptcha/admin para el dominio del sitio.')
                                ->columns(2)
                                ->schema([
                                    Toggle::make('recaptcha_enabled')->label('Proteger el formulario de contacto')->columnSpanFull(),
                                    TextInput::make('recaptcha_site_key')->label('Clave del sitio')->maxLength(255)->required(fn (Get $get): bool => (bool) $get('recaptcha_enabled')),
                                    TextInput::make('recaptcha_secret_key')->label('Clave secreta')->password()->revealable()->maxLength(255)
                                        ->placeholder(fn (): string => filled(SiteSetting::current()->recaptcha_secret_key) ? '•••••••• (guardada)' : '')
                                        ->required(fn (Get $get): bool => $get('recaptcha_enabled') && blank(SiteSetting::current()->recaptcha_secret_key))
                                        ->helperText('Vacío conserva la clave guardada.'),
                                    TextInput::make('recaptcha_min_score')->label('Puntaje mínimo')->numeric()->minValue(0.1)->maxValue(1)->step(0.1)->required()
                                        ->helperText('De 0.1 a 1. Con 0.5 se bloquea la mayoría de bots sin afectar a personas.'),
                                ]),
                        ]),
                        Tab::make('Analytics y Ads')->icon(Heroicon::OutlinedChartBar)->schema([
                            Section::make('Google y Meta')
                                ->description('Solo el ID de cada herramienta. Las etiquetas se cargan cuando la persona acepta las cookies de analítica o marketing.')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('google_analytics_id')->label('Google Analytics 4')->placeholder('G-XXXXXXXXXX')->regex('/^G-[A-Z0-9]+$/')->maxLength(40),
                                    TextInput::make('google_tag_manager_id')->label('Google Tag Manager')->placeholder('GTM-XXXXXXX')->regex('/^GTM-[A-Z0-9]+$/')->maxLength(40),
                                    TextInput::make('meta_pixel_id')->label('Meta Pixel')->placeholder('123456789012345')->regex('/^[0-9]+$/')->maxLength(40),
                                    TextInput::make('google_ads_id')->label('Google Ads')->placeholder('AW-123456789')->regex('/^AW-[0-9]+$/')->maxLength(40),
                                ]),
                            Section::make('LinkedIn Insight Tag')
                                ->description('Mide visitas y conversiones de campañas en LinkedIn. Cada conversión se crea en Campaign Manager y se pega aquí su ID.')
                                ->columns(2)
                                ->schema([
                                    Toggle::make('linkedin_enabled')->label('Activar LinkedIn Insight Tag')->columnSpanFull(),
                                    TextInput::make('linkedin_partner_id')->label('Partner ID')->regex('/^[0-9]{1,20}$/')->required(fn (Get $get): bool => (bool) $get('linkedin_enabled')),
                                    TextInput::make('linkedin_contact_conversion_id')->label('Conversión: clic en "Hablemos"')->regex('/^[0-9]{1,20}$/'),
                                    TextInput::make('linkedin_form_start_conversion_id')->label('Conversión: empezó el formulario')->regex('/^[0-9]{1,20}$/'),
                                    TextInput::make('linkedin_form_submit_conversion_id')->label('Conversión: envió el formulario')->regex('/^[0-9]{1,20}$/'),
                                    TextInput::make('linkedin_lead_conversion_id')->label('Conversión: lead')->regex('/^[0-9]{1,20}$/'),
                                ]),
                            Section::make('Scripts personalizados')
                                ->description('Para otras herramientas (Hotjar, TikTok, Clarity…). Se cargan como marketing, después del consentimiento de cookies. Los de Google y Meta con los IDs de arriba se omiten para no duplicarlos.')
                                ->schema([
                                    Textarea::make('custom_head_scripts')->label('Scripts en <head>')->rows(6),
                                    Textarea::make('custom_body_scripts')->label('Scripts al final de <body>')->rows(6),
                                ]),
                        ]),
                    ]),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')->label('Guardar configuración')->submit('save'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $settings = SiteSetting::current();

        foreach (['mail_password', 'recaptcha_secret_key'] as $secret) {
            if (blank($state[$secret] ?? null)) {
                unset($state[$secret]);
            }
        }

        $state['mail_to_address'] = implode(',', $state['mail_to_address'] ?? []);
        $settings->update($state);

        $this->form->fill([...$this->data, 'mail_password' => null, 'recaptcha_secret_key' => null]);

        Notification::make()->title('Configuración guardada')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendTestMail')
                ->label('Enviar correo de prueba')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('Se envía con la configuración guardada a los correos del equipo. Guarda antes los cambios que acabas de hacer.')
                ->action(function (): void {
                    $settings = SiteSetting::current();

                    if (! $settings->canSendMail() || $settings->mailRecipients() === []) {
                        Notification::make()->title('Activa el envío y agrega al menos un correo del equipo.')->warning()->send();

                        return;
                    }

                    try {
                        $settings->applyMailConfiguration();
                        Mail::to($settings->mailRecipients())->send(new SettingsTestMail($settings));
                        Notification::make()->title('Correo de prueba enviado a '.implode(', ', $settings->mailRecipients()))->success()->send();
                    } catch (Throwable $exception) {
                        Notification::make()->title('No se pudo enviar el correo')->body($exception->getMessage())->danger()->persistent()->send();
                    }
                }),
        ];
    }
}
