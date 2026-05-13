<?php

/**
 * Filament 2.x configuration overrides
 * Run: php artisan vendor:publish --tag=filament-config
 * แล้วแก้ค่าตามนี้
 */
return [
    'broadcasting' => false,
    'path'         => env('FILAMENT_PATH', 'admin'),
    'core_path'    => env('FILAMENT_CORE_PATH', 'filament'),
    'domain'       => env('FILAMENT_DOMAIN'),

    'home_url' => '/',

    'brand' => env('APP_NAME', 'RUS Research CMS'),

    'auth' => [
        'guard' => env('FILAMENT_AUTH_GUARD', 'web'),
    ],

    'pages' => [
        'namespace' => 'App\\Filament\\Pages',
        'path'      => app_path('Filament/Pages'),
        'register'  => [],
    ],

    'resources' => [
        'namespace' => 'App\\Filament\\Resources',
        'path'      => app_path('Filament/Resources'),
        'register'  => [],
    ],

    'widgets' => [
        'namespace' => 'App\\Filament\\Widgets',
        'path'      => app_path('Filament/Widgets'),
        'register'  => [],
    ],

    'livewire' => [
        'namespace' => 'App\\Http\\Livewire',
        'path'      => app_path('Http/Livewire'),
    ],

    'dark_mode' => false,

    'database_notifications' => [
        'enabled' => false,
    ],

    'middleware' => [
        'auth' => [
            \Filament\Http\Middleware\Authenticate::class,
        ],
        'base' => [
            'web',
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\SetLocale::class,
        ],
    ],

    'layout' => [
        'forms' => [
            'have_inline_labels' => false,
        ],
        'tables' => [
            'actions' => [
                'type' => \Filament\Tables\Actions\LinkAction::class,
            ],
        ],
    ],
];
