<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

/**
 * Country of an IP address for the markets the site serves, read from the DB-IP Lite ranges saved by `geoip:update`.
 * Each file holds sorted fixed-size records: start and end address (packed, network order) and the country index.
 */
class IpCountryLookup
{
    /** @var array<int, string> */
    public const COUNTRIES = ['GT', 'CR', 'SV', 'US'];

    public function __construct(private ?string $directory = null) {}

    public function directory(): string
    {
        return $this->directory ?? storage_path('app/geoip');
    }

    public function country(?string $ip): ?string
    {
        $address = filled($ip) ? @inet_pton($ip) : false;
        $file = $address === false ? null : $this->directory().'/'.(strlen($address) === 4 ? 'ipv4' : 'ipv6').'.bin';

        if ($file === null || ! is_file($file)) {
            return null;
        }

        $length = strlen($address);
        $recordSize = $length * 2 + 1;
        $match = null;
        $handle = fopen($file, 'rb');

        try {
            $low = 0;
            $high = intdiv((int) filesize($file), $recordSize) - 1;

            while ($low <= $high) {
                $middle = intdiv($low + $high, 2);
                fseek($handle, $middle * $recordSize);
                $record = (string) fread($handle, $recordSize);

                if (strcmp(substr($record, 0, $length), $address) <= 0) {
                    $match = $record;
                    $low = $middle + 1;
                } else {
                    $high = $middle - 1;
                }
            }
        } finally {
            fclose($handle);
        }

        if ($match === null || strcmp(substr($match, $length, $length), $address) < 0) {
            return null;
        }

        return self::COUNTRIES[ord($match[$recordSize - 1])] ?? null;
    }

    /**
     * Replaces the range files with the rows of the served countries. Nothing is written when no row matches.
     *
     * @param  iterable<int, array<int, string>>  $rows  DB-IP rows: start address, end address, country code.
     * @return array{ipv4: int, ipv6: int}
     */
    public function build(iterable $rows): array
    {
        $records = ['ipv4' => [], 'ipv6' => []];

        foreach ($rows as $row) {
            $index = array_search(strtoupper(trim($row[2] ?? '')), self::COUNTRIES, true);
            $start = @inet_pton(trim($row[0] ?? ''));
            $end = @inet_pton(trim($row[1] ?? ''));

            if ($index === false || $start === false || $end === false || strlen($start) !== strlen($end)) {
                continue;
            }

            $records[strlen($start) === 4 ? 'ipv4' : 'ipv6'][] = $start.$end.chr($index);
        }

        $counts = ['ipv4' => count($records['ipv4']), 'ipv6' => count($records['ipv6'])];

        if (array_sum($counts) === 0) {
            return $counts;
        }

        File::ensureDirectoryExists($this->directory());

        foreach ($records as $family => $list) {
            sort($list, SORT_STRING);
            $path = $this->directory().'/'.$family.'.bin';
            File::put($path.'.tmp', implode('', $list));
            File::move($path.'.tmp', $path);
        }

        return $counts;
    }
}
