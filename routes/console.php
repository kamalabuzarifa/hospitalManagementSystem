<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\App;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('check:translations', function () {
    $keys = ['name', 'email', 'position', 'department', 'status', 'actions'];

    $this->info('Checking translations for messages.* keys');

    App::setLocale('en');
    $this->line('Locale: en');
    foreach ($keys as $key) {
        $this->line($key . ' => ' . __('messages.' . $key));
    }

    $this->newLine();

    App::setLocale('ar');
    $this->line('Locale: ar');
    foreach ($keys as $key) {
        $this->line($key . ' => ' . __('messages.' . $key));
    }
})->purpose('Verify staff table header translations for en/ar');
