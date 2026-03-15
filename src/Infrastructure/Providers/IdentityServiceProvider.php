<?php

namespace Src\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Domain\Identity\Contracts\CustomerRepositoryContract;
use Src\Infrastructure\Persistence\Identity\EloquentCustomerRepository;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CustomerRepositoryContract::class, EloquentCustomerRepository::class);
    }
}
