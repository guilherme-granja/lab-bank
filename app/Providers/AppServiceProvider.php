<?php

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Src\Infrastructure\Auth\PersonalAccessToken;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }

    public function boot(): void
    {
        $this->domainEventsTable();

        $this->loadMigrationsFrom([
            database_path('migrations/accounts'),
            database_path('migrations/cards'),
            database_path('migrations/identity'),
            database_path('migrations/investments'),
            database_path('migrations/app'),
        ]);
    }

    private function domainEventsTable(): void
    {
        Blueprint::macro('domainEvents', static function () {
            Schema::create('domain_events', function (Blueprint $table) {
                $table->uuid('id')->primary();

                $table->uuid('aggregate_type');
                $table->uuid('aggregate_id');
                $table->integer('aggregate_version');
                $table->string('event_type');
                $table->json('payload');
                $table->json('metadata')->nullable();
                $table->timestamp('occurred_at');

                $table->unique(['aggregate_id', 'aggregate_version']);
            });
        });
    }
}
