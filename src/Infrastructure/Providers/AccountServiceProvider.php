<?php

namespace Src\Infrastructure\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Src\Domain\Accounts\Contracts\AccountBalanceRepositoryContract;
use Src\Domain\Accounts\Contracts\AccountRepositoryContract;
use Src\Infrastructure\Persistence\Accounts\EloquentAccountBalanceRepository;
use Src\Infrastructure\Persistence\Accounts\EloquentAccountRepository;
use Src\Interfaces\Events\Account\AccountWasOpened;
use Src\Interfaces\Listeners\PersistDomainEvent;

class AccountServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AccountRepositoryContract::class, EloquentAccountRepository::class);
        $this->app->bind(AccountBalanceRepositoryContract::class, EloquentAccountBalanceRepository::class);
    }

    public function boot(): void
    {
        Event::listen([
            AccountWasOpened::class,
        ], PersistDomainEvent::class);
    }
}
