<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('seo:audit --no-interaction')->dailyAt('04:00')->timezone('America/Guatemala')->withoutOverlapping(30);
Schedule::command('geoip:update --no-interaction')->monthlyOn(5, '04:30')->timezone('America/Guatemala')->withoutOverlapping(30);

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
