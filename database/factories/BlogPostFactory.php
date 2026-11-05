<?php

namespace Database\Factories;

use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BlogPost> */
class BlogPostFactory extends Factory
{
    public function definition(): array
    {
        $slug = fake()->unique()->slug();

        return ['path' => '/post/'.$slug, 'slug' => $slug, 'locale' => 'es', 'title' => fake()->sentence(), 'excerpt' => fake()->paragraph(), 'content' => '<p>Contenido del artículo de prueba.</p>', 'author' => fake()->name(), 'published_at' => now()->subDay(), 'reading_minutes' => 2, 'is_published' => true, 'tags' => [], 'translations' => []];
    }
}
