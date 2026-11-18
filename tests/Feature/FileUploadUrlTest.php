<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageNavigation;
use App\Models\PageSection;
use App\Models\User;
use App\Services\SiteContent;
use Filament\Forms\Components\FileUpload;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FileUploadUrlTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_saved_images_load_from_whichever_host_the_panel_is_open_on(): void
    {
        config(['app.url' => 'http://localhost:8000']);
        Storage::fake('public', ['url' => 'http://localhost:8000/storage']);
        UploadedFile::fake()->image('logo.png')->storeAs('site/logos', 'logo.png', 'public');
        PageSection::query()->create(['page' => SiteContent::PAGE, 'section' => SiteContent::MEDIA, 'content_es' => ['logo_light' => 'site/logos/logo.png'], 'content_en' => []]);
        $this->actingAs(User::factory()->create());

        $form = Livewire::test(ManageNavigation::class)->instance()->form;
        $upload = $form->getComponent(fn ($component): bool => $component instanceof FileUpload && $component->getName() === 'logo_light', withHidden: true);

        $this->assertSame(['/storage/site/logos/logo.png'], array_column($upload->getUploadedFiles(), 'url'));
    }
}
