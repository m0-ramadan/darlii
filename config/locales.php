<?php
return [
    'supported' => ['ar', 'en'],
    'default' => env('APP_LOCALE', 'ar'),
    'rtl' => ['ar'],
    'names' => ['ar' => 'العربية', 'en' => 'English'],
    'labels' => ['ar' => 'اللغة', 'en' => 'Language'],
    // Temporary dashboard switch: keep existing English translations, but do not
    // expose fields that create or update them from the admin panel.
    'admin_english_enabled' => env('ADMIN_ENGLISH_ENABLED', false),
];
