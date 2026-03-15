<?php

namespace Src\Application\Identity\Handlers;

use Src\Domain\Identity\Contracts\CustomerRepositoryContract;
use Src\Infrastructure\EventStore\EventStoreRepository;

readonly class RegisterCustomer
{
    public function __construct(
        protected CustomerRepositoryContract $customerRepository,
        protected EventStoreRepository $eventStoreRepository,
    ) {}
}
