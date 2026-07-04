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
        // Dynamically point SQLite database to writeable /tmp on Vercel (Linux serverless)
        if (file_exists('/tmp') && is_dir('/tmp') && is_writable('/tmp')) {
            config(['database.connections.sqlite.database' => '/tmp/database.sqlite']);
        }
    }
}
