<?php

namespace App\Console\Commands;

use App\Services\BlogImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('blog:import {--overwrite : Reemplaza los artículos existentes con la copia original}')]
#[Description('Importa los artículos originales de Feat. conservando sus URLs y contenido')]
class ImportBlog extends Command
{
    public function handle(BlogImporter $importer): int
    {
        $result = $importer->import((bool) $this->option('overwrite'));
        $this->info("Blog: {$result['created']} artículos importados, {$result['skipped']} conservados y {$result['categories']} categorías.");

        return self::SUCCESS;
    }
}
