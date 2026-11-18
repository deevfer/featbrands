<?php

namespace App\Filament\Components;

use Closure;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;

/**
 * How the page would look in a search result, updated while the title and description are typed.
 */
class SearchSnippet
{
    /**
     * @param  array<int, string>  $titleFields  First filled field wins (e.g. SEO title, then the post title).
     * @param  array<int, string>  $descriptionFields
     * @param  Closure(mixed): string  $url  Receives the record being edited.
     */
    public static function make(array $titleFields, array $descriptionFields, Closure $url): View
    {
        $first = fn (Get $get, array $fields): string => collect($fields)->map(fn (string $field): string => trim(strip_tags((string) $get($field))))->first(fn (string $value): bool => $value !== '') ?? '';

        return View::make('filament.components.search-snippet')
            ->viewData(fn (Get $get, mixed $record = null): array => [
                'title' => $first($get, $titleFields),
                'description' => $first($get, $descriptionFields),
                'url' => $url($record),
            ])
            ->columnSpanFull();
    }
}
