<?php

namespace Database\Factories;

use App\Models\BlogCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BlogCategory> */
class BlogCategoryFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->words(2, true), 'slug' => fake()->unique()->slug(), 'position' => 0];
    }
}
