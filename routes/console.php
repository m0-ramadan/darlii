<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('about:project', function () {
    $this->info('Darlii modular rebuild');
})->purpose('Show project identity');
Schedule::command('sitemap:generate')->everyTenMinutes()->withoutOverlapping();
Schedule::command('finance:run-recurring-points')->hourly()->withoutOverlapping();
Schedule::command('queue:prune-batches --hours=48')->daily();
Schedule::command('queue:prune-failed --hours=168')->daily();
