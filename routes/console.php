<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('hotspot:sync')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('hotspot:recover-payments')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('hotspot:permanent-sync')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('hotspot:permanent-reminders')
    ->dailyAt('16:00')
    ->timezone('Africa/Dar_es_Salaam')
    ->withoutOverlapping();

Schedule::command('hotspot:archive-inactive-customers --days=3')
    ->hourly()
    ->withoutOverlapping();
