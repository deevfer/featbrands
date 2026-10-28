<?php

namespace Database\Seeders;

use App\Models\PageSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class HomeProofSeeder extends Seeder
{
    public function run(): void
    {
        $site = File::json(resource_path('data/site.json'));

        $images = [];

        for ($i = 0; $i < count($site['es']['proof']); $i++) {
            $source = public_path("uploads/proof-bg-{$i}.webp");
            $destination = "home/proof/original-{$i}.webp";

            if (
                File::exists($source) &&
                !Storage::disk('public')->exists($destination)
            ) {
                Storage::disk('public')->put(
                    $destination,
                    File::get($source)
                );
            }

            $images[$i] = Storage::disk('public')->exists($destination)
                ? $destination
                : null;
        }

        $alts = [
            'es' => [
                'Ilustración de campaña con elementos de colores',
                'Perro en un parque sobre el césped',
                'Campaña Tigo Refréscate con personaje en la playa',
            ],
            'en' => [
                'Campaign illustration with colorful elements',
                'Dog sitting on the grass in a park',
                'Tigo Refrescate campaign with a character on the beach',
            ],
        ];

        $content = [];

        foreach (['es', 'en'] as $lang) {
            $cards = [];

            foreach ($site[$lang]['proof'] as $index => $card) {
                $cards[] = [
                    'big' => $card['big'],
                    'label' => $card['label'],
                    'image' => $images[$index] ?? null,
                    'image_alt' => $alts[$lang][$index] ?? '',
                ];
            }

            $content[$lang] = [
                'eyebrow' => $site[$lang]['proofKicker'],
                'title' => $site[$lang]['proofTitle'],
                'cards' => $cards,
            ];
        }

        PageSection::firstOrCreate(
            [
                'page' => 'home',
                'section' => 'proof',
            ],
            [
                'content_es' => $content['es'],
                'content_en' => $content['en'],
            ]
        );
    }
}
