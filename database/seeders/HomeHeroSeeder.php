<?php

namespace Database\Seeders;

use App\Models\PageSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class HomeHeroSeeder extends Seeder
{
    public function run(): void
    {
        $site = File::json(resource_path('data/site.json'));

        PageSection::firstOrCreate(
            [
                'page' => 'home',
                'section' => 'hero',
            ],
            [
                'content_es' => $site['es']['hero'],
                'content_en' => $site['en']['hero'],
            ]
        );
    }
}
