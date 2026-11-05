<?php

namespace App\Console\Commands;

use App\Services\SeoAuditor;
use App\Services\SeoPublicVerifier;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('seo:audit {--verify-public : Comprueba el dominio HTTPS antes de analizar} {--json : Devuelve el resumen en JSON}')]
#[Description('Analiza el SEO técnico, las páginas y los artículos y guarda la puntuación en el CMS')]
class AuditSeo extends Command
{
    public function handle(SeoAuditor $auditor): int
    {
        if ($this->option('verify-public')) {
            app(SeoPublicVerifier::class)->verify();
        }
        $audit = $auditor->run();
        $summary = ['score' => $audit->score, 'pages' => $audit->page_count, 'site' => $audit->report['site_score'], 'corporate' => $audit->report['pages_score'], 'articles' => $audit->report['articles_score'], 'checked_at' => $audit->report['checked_at']];
        if ($this->option('json')) {
            $this->line(json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info('Optimización: '.$audit->score.'/100 · '.$audit->page_count.' páginas analizadas.');
            $this->line('Ver el detalle en /admin/seo-dashboard. La nota es interna y no representa un ranking de los buscadores.');
        }

        return self::SUCCESS;
    }
}
