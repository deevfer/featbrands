<?php

namespace Tests\Feature;

use App\Filament\Pages\SeoSettings;
use App\Models\BlogPost;
use App\Models\SeoSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\InspectsStructuredData;
use Tests\TestCase;

class OrganizationSchemaTest extends TestCase
{
    use InspectsStructuredData, LazilyRefreshDatabase;

    /** @return array<string, array{string}> */
    public static function urls(): array
    {
        return ['home' => ['/'], 'servicio' => ['/makitha'], 'página GEO' => ['/mejor-agencia-publicidad-guatemala'], 'blog' => ['/blog'], 'post' => ['/post/organizacion']];
    }

    #[DataProvider('urls')]
    public function test_every_page_has_a_single_extended_organization_without_same_as_by_default(string $url): void
    {
        BlogPost::factory()->create(['slug' => 'organizacion', 'path' => '/post/organizacion']);

        $graph = $this->schemaGraph($this->document($this->get($url)->assertOk()->getContent()));
        $organizations = array_values(array_filter($graph, fn (array $node): bool => $node['@type'] === 'Organization'));

        $this->assertCount(1, $organizations);
        $organization = $organizations[0];
        $this->assertSame('Feat.', $organization['name']);
        $this->assertSame('https://www.featbrands.com/#organization', $organization['@id']);
        $this->assertSame('Agencia de publicidad integral con sede en Ciudad de Guatemala y oficina en Costa Rica.', $organization['description']);
        $this->assertSame(['@type' => 'PostalAddress', 'addressLocality' => 'Ciudad de Guatemala', 'addressCountry' => 'GT'], $organization['address']);
        $this->assertSame(['Guatemala', 'Costa Rica', 'Centroamérica'], $organization['areaServed']);
        $this->assertSame(['@type' => 'Organization', 'name' => 'Comunidad de Empresas de Comunicación de Guatemala'], $organization['memberOf']);
        $this->assertSame(['Effie Award (tres veces desde 2017)', 'DesignRush Top Digital Marketing Agency 2025'], $organization['award']);
        $this->assertArrayNotHasKey('sameAs', $organization);
    }

    public function test_same_as_comes_only_from_the_seo_settings_panel(): void
    {
        $this->actingAs(User::factory()->create());
        Livewire::test(SeoSettings::class)
            ->fillForm(['same_as' => ['https://www.linkedin.com/company/ejemplo']])
            ->call('save')
            ->assertHasNoFormErrors();
        SeoSetting::find(1)->update(['data' => [...SeoSetting::find(1)->data, 'same_as' => ['https://www.linkedin.com/company/ejemplo', 'javascript:alert(1)']]]);

        $graph = $this->schemaGraph($this->document($this->get('/')->getContent()));

        $this->assertSame(['https://www.linkedin.com/company/ejemplo'], collect($graph)->firstWhere('@type', 'Organization')['sameAs']);
    }

    public function test_settings_reject_profiles_that_are_not_https_urls(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(SeoSettings::class)
            ->fillForm(['same_as' => ['http://perfil.example.com', '<URL de LinkedIn de Feat. — completar>']])
            ->call('save')
            ->assertHasFormErrors(['same_as.0', 'same_as.1']);

        $this->assertDatabaseCount('seo_settings', 0);
    }
}
