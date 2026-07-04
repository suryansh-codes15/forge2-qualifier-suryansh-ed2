<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Dynamically point SQLite database to writeable /tmp in production (Vercel)
        if (config('app.env') === 'production' || env('APP_ENV') === 'production' || getenv('APP_ENV') === 'production') {
            config(['database.connections.sqlite.database' => '/tmp/database.sqlite']);
        }
    }
}
