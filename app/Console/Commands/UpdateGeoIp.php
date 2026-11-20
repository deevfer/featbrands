<?php

namespace App\Console\Commands;

use App\Services\IpCountryLookup;
use Generator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

#[Signature('geoip:update {--file= : CSV de DB-IP ya descargado (.csv o .csv.gz) en lugar de descargarlo}')]
#[Description('Descarga DB-IP Lite y guarda los rangos IP de Guatemala, Costa Rica, El Salvador y Estados Unidos')]
class UpdateGeoIp extends Command
{
    public function handle(IpCountryLookup $lookup): int
    {
        $path = $this->option('file') ?: $this->download();

        if ($path === null || ! is_file($path)) {
            $this->error('No se pudo obtener la base de DB-IP. Se conservan los rangos guardados.');

            return self::FAILURE;
        }

        try {
            $counts = $lookup->build($this->rows($path));
        } finally {
            if (! $this->option('file')) {
                File::delete($path);
            }
        }

        if (array_sum($counts) === 0) {
            $this->error('El archivo no tiene rangos de GT, CR, SV ni US. Se conservan los rangos guardados.');

            return self::FAILURE;
        }

        $this->info("Rangos guardados: {$counts['ipv4']} IPv4 y {$counts['ipv6']} IPv6. IP Geolocation by DB-IP (db-ip.com).");

        return self::SUCCESS;
    }

    /**
     * DB-IP publishes each month's file early in the month, so the previous one is the fallback.
     */
    private function download(): ?string
    {
        $target = storage_path('app/geoip-download.csv.gz');

        foreach ([now(), now()->subMonthNoOverflow()] as $month) {
            try {
                $response = Http::timeout(180)->sink($target)->get('https://download.db-ip.com/free/dbip-country-lite-'.$month->format('Y-m').'.csv.gz');

                if ($response->successful()) {
                    return $target;
                }
            } catch (Throwable) {
            }
        }

        File::delete($target);

        return null;
    }

    /**
     * @return Generator<int, array<int, string>>
     */
    private function rows(string $path): Generator
    {
        $handle = fopen((str_ends_with($path, '.gz') ? 'compress.zlib://' : '').$path, 'rb');

        try {
            while (($row = fgetcsv($handle, escape: '')) !== false) {
                if (count($row) >= 3) {
                    yield array_map('strval', $row);
                }
            }
        } finally {
            fclose($handle);
        }
    }
}
