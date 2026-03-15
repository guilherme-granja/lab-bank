<?php

namespace Src\Infrastructure\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Src\Domain\Identity\Contracts\CustomerRepositoryContract;
use Src\Infrastructure\Persistence\Identity\EloquentCustomerRepository;
use Src\Interfaces\Events\Identity\CustomerWasBlocked;
use Src\Interfaces\Events\Identity\CustomerWasRegistered;
use Src\Interfaces\Events\Identity\KycWasApproved;
use Src\Interfaces\Events\Identity\KycWasRejected;
use Src\Interfaces\Listeners\PersistDomainEvent;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CustomerRepositoryContract::class, EloquentCustomerRepository::class);
    }

    public function boot(): void
    {
        Event::listen([
            CustomerWasRegistered::class,
            CustomerWasBlocked::class,
            KycWasApproved::class,
            KycWasRejected::class,
        ], PersistDomainEvent::class);
    }
}
