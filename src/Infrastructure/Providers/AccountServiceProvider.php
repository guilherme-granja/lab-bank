<?php

namespace Src\Infrastructure\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Src\Interfaces\Events\Account\AccountWasOpened;
use Src\Interfaces\Events\Account\FundsWereDeposited;
use Src\Interfaces\Listeners\PersistDomainEvent;

class AccountServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen([
            AccountWasOpened::class,
            FundsWereDeposited::class,
        ], PersistDomainEvent::class);
    }
}
