<?php

namespace App\Filament\Concerns;

use App\Services\PagePreview;
use Filament\Support\Enums\Width;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;

/**
 * Side-by-side preview of the public page with the form's unsaved changes.
 */
trait HasLivePreview
{
    /**
     * Public path to preview; $statePath is the field being edited (e.g. "data.c1.es.h1").
     */
    abstract protected function previewPath(?string $statePath = null): string;

    /**
     * Writes the draft exactly as saving would; it is rolled back right after rendering.
     */
    protected function applyPreviewDraft(): void
    {
        $this->save();
    }

    protected function afterPreview(): void {}

    /**
     * The form and the preview need the whole width.
     */
    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    protected function previewIsLocalized(): bool
    {
        return true;
    }

    /**
     * Renderless: the form does not change, so Livewire does not morph it on every refresh.
     *
     * @return array{html: string|null, status: int, path: string}
     */
    #[Renderless]
    public function renderPreview(string $locale = 'es', ?string $statePath = null): array
    {
        $path = '/';

        try {
            $result = app(PagePreview::class)->render(function () use ($statePath, $locale, &$path): string {
                $this->applyPreviewDraft();
                $path = $this->previewPath($statePath);
                $path = $this->previewIsLocalized() ? $this->withLocale($path, $locale) : $path;

                return $path;
            });
        } catch (ValidationException) {
            $result = ['html' => null, 'status' => 422];
        } finally {
            session()->forget('filament.notifications');
            $this->afterPreview();
        }

        return [...$result, 'path' => $path];
    }

    /**
     * @return array{localized: bool, publicUrl: string}
     */
    public function previewConfig(): array
    {
        return [
            'localized' => $this->previewIsLocalized(),
            'publicUrl' => url($this->previewPath()),
        ];
    }

    private function withLocale(string $path, string $locale): string
    {
        return $path.(str_contains($path, '?') ? '&' : '?').'lang='.($locale === 'en' ? 'en' : 'es');
    }
}
