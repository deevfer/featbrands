<?php

namespace Database\Factories;

use App\Models\SeoPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SeoPage> */
class SeoPageFactory extends Factory
{
    public function definition(): array
    {
        return ['route_name' => 'home', 'locale' => 'es', 'title' => 'Estrategia, creatividad y tecnología | Feat.', 'description' => 'Conoce los servicios de estrategia, creatividad y tecnología de Feat. y descubre cómo conectamos marcas y negocios.'];
    }
}
