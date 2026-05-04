<?php

namespace Src\Infrastructure\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Src\Domain\Accounts\Contracts\AccountBalanceRepositoryContract;
use Src\Domain\Accounts\Contracts\AccountRepositoryContract;
use Src\Domain\Accounts\Contracts\LedgerEntryRepositoryContract;
use Src\Domain\Accounts\Contracts\TransactionRepositoryContract;
use Src\Infrastructure\Persistence\Accounts\EloquentAccountBalanceRepository;
use Src\Infrastructure\Persistence\Accounts\EloquentAccountRepository;
use Src\Infrastructure\Persistence\Accounts\EloquentLedgerEntryRepository;
use Src\Infrastructure\Persistence\Accounts\EloquentTransactionRepository;
use Src\Interfaces\Events\Account\AccountWasOpened;
use Src\Interfaces\Events\Account\FundsWereDeposited;
use Src\Interfaces\Listeners\PersistDomainEvent;

class AccountServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AccountRepositoryContract::class, EloquentAccountRepository::class);
        $this->app->bind(AccountBalanceRepositoryContract::class, EloquentAccountBalanceRepository::class);
        $this->app->bind(TransactionRepositoryContract::class, EloquentTransactionRepository::class);
        $this->app->bind(LedgerEntryRepositoryContract::class, EloquentLedgerEntryRepository::class);
    }

    public function boot(): void
    {
        Event::listen([
            AccountWasOpened::class,
            FundsWereDeposited::class,
        ], PersistDomainEvent::class);
    }
}
