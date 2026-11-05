<?php

namespace App\Services;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BlogImporter
{
    /** @return array{created: int, skipped: int, categories: int} */
    public function import(bool $overwrite = false): array
    {
        $catalog = File::json(resource_path('data/blog/catalog.json'));

        return DB::transaction(function () use ($catalog, $overwrite): array {
            $categories = [];
            foreach ($catalog['categories'] as $category) {
                $record = BlogCategory::firstOrCreate(['slug' => $category['slug']], $category);
                $categories[$category['slug']] = $record->id;
            }
            $created = 0;
            $skipped = 0;
            foreach ($catalog['posts'] as $data) {
                $post = BlogPost::firstOrNew(['path' => $data['path']]);
                if ($post->exists && ! $overwrite) {
                    $skipped++;

                    continue;
                }
                $slugs = $data['category_slugs'];
                unset($data['category_slugs']);
                $post->fill($data)->save();
                $post->categories()->sync(array_map(fn (string $slug): int => $categories[$slug], $slugs));
                $created++;
            }

            return ['created' => $created, 'skipped' => $skipped, 'categories' => count($categories)];
        });
    }
}
