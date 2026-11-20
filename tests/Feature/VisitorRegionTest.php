<?php

namespace Tests\Feature;

use App\Services\IpCountryLookup;
use App\Services\VisitorRegion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VisitorRegionTest extends TestCase
{
    use RefreshDatabase;

    private string $geoDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->geoDirectory = storage_path('framework/testing/geoip-'.uniqid());
        $lookup = new IpCountryLookup($this->geoDirectory);
        $lookup->build([
            ['1.0.0.0', '1.0.0.255', 'AU'],
            ['8.8.8.0', '8.8.8.255', 'US'],
            ['181.114.0.0', '181.114.255.255', 'GT'],
            ['190.5.0.0', '190.5.255.255', 'CR'],
            ['2800:860::', '2800:860:ffff:ffff:ffff:ffff:ffff:ffff', 'CR'],
            ['2001:4860::', '2001:4860:ffff:ffff:ffff:ffff:ffff:ffff', 'US'],
        ]);
        $this->app->instance(IpCountryLookup::class, $lookup);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->geoDirectory);

        parent::tearDown();
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function redirectedCountries(): array
    {
        return [
            'Estados Unidos' => ['US', 'en', 'us'],
            'Costa Rica' => ['CR', 'es', 'cr'],
            'El Salvador' => ['SV', 'es', 'sv'],
        ];
    }

    #[DataProvider('redirectedCountries')]
    public function test_first_visit_opens_in_the_language_and_market_of_the_country(string $country, string $lang, string $market): void
    {
        $this->get('/nosotros', ['CF-IPCountry' => $country])
            ->assertRedirect(route('about', ['lang' => $lang, 'market' => $market]))
            ->assertCookie(VisitorRegion::COOKIE, $lang.'|'.$market);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function spanishGuatemalaCountries(): array
    {
        return ['Guatemala' => ['GT'], 'otro país' => ['FR'], 'sin país' => ['XX']];
    }

    #[DataProvider('spanishGuatemalaCountries')]
    public function test_guatemala_and_other_countries_stay_in_spanish_and_guatemala(string $country): void
    {
        $this->get('/', ['CF-IPCountry' => $country])
            ->assertOk()
            ->assertSee('<html lang="es">', false)
            ->assertSee('data-market="gt"', false)
            ->assertCookie(VisitorRegion::COOKIE, 'es|gt');
    }

    public function test_language_toggle_keeps_working_and_becomes_the_saved_choice(): void
    {
        $this->get('/?lang=es&market=us', ['CF-IPCountry' => 'US'])
            ->assertOk()
            ->assertSee('<html lang="es">', false)
            ->assertSee('data-market="us"', false)
            ->assertSee('data-language="en"', false)
            ->assertCookie(VisitorRegion::COOKIE, 'es|us');

        $this->withCookie(VisitorRegion::COOKIE, 'es|us')
            ->get('/', ['CF-IPCountry' => 'US'])
            ->assertRedirect(route('home', ['lang' => 'es', 'market' => 'us']));
    }

    public function test_crawlers_are_never_redirected(): void
    {
        $this->get('/', ['CF-IPCountry' => 'US', 'User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])
            ->assertOk()
            ->assertSee('<html lang="es">', false)
            ->assertCookieMissing(VisitorRegion::COOKIE);
    }

    public function test_spanish_only_pages_are_not_redirected(): void
    {
        $this->get('/mejor-agencia-publicidad-guatemala', ['CF-IPCountry' => 'US'])->assertOk();
    }

    public function test_without_cloudflare_the_local_ip_ranges_find_the_country(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->get('/')
            ->assertRedirect(route('home', ['lang' => 'en', 'market' => 'us']));

        $this->withServerVariables(['REMOTE_ADDR' => '2800:860::1'])->get('/')
            ->assertRedirect(route('home', ['lang' => 'es', 'market' => 'cr']));

        $this->withServerVariables(['REMOTE_ADDR' => '1.0.0.8'])->get('/')->assertOk();
    }

    public function test_lookup_matches_only_inside_the_saved_ranges(): void
    {
        $lookup = app(IpCountryLookup::class);

        $this->assertSame('GT', $lookup->country('181.114.20.4'));
        $this->assertSame('US', $lookup->country('8.8.8.255'));
        $this->assertSame('US', $lookup->country('2001:4860:4860::8888'));
        $this->assertNull($lookup->country('8.8.9.0'));
        $this->assertNull($lookup->country('1.0.0.8'));
        $this->assertNull($lookup->country('not-an-ip'));
        $this->assertNull((new IpCountryLookup($this->geoDirectory.'/missing'))->country('8.8.8.8'));
    }

    public function test_update_command_rebuilds_the_ranges_from_a_db_ip_file(): void
    {
        $csv = $this->geoDirectory.'/dbip.csv.gz';
        File::put($csv, gzencode("0.0.0.0,0.255.255.255,ZZ\n200.6.0.0,200.6.255.255,SV\n9.9.9.0,9.9.9.255,US\n"));

        $this->artisan('geoip:update', ['--file' => $csv])
            ->expectsOutputToContain('Rangos guardados: 2 IPv4 y 0 IPv6.')
            ->assertSuccessful();

        $lookup = app(IpCountryLookup::class);
        $this->assertSame('SV', $lookup->country('200.6.1.1'));
        $this->assertNull($lookup->country('8.8.8.8'));
    }

    public function test_update_command_keeps_the_ranges_when_the_file_has_no_served_country(): void
    {
        $csv = $this->geoDirectory.'/empty.csv';
        File::put($csv, "1.0.0.0,1.0.0.255,AU\n");

        $this->artisan('geoip:update', ['--file' => $csv])->assertFailed();

        $this->assertSame('US', app(IpCountryLookup::class)->country('8.8.8.8'));
    }
}
