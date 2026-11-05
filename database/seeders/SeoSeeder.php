<?php

namespace Database\Seeders;

use App\Services\SiteSeo;
use Illuminate\Database\Seeder;

class SeoSeeder extends Seeder
{
    public function run(SiteSeo $seo): void
    {
        $seo->initialize();
    }
}
