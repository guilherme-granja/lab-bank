<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom([
            database_path('migrations/accounts'),
            database_path('migrations/cards'),
            database_path('migrations/identity'),
            database_path('migrations/investments'),
        ]);
    }
}
