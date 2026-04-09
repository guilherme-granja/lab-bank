<?php

namespace Src\Interfaces\Events\Identity;

use Illuminate\Foundation\Events\Dispatchable;
use Src\Domain\Identity\Events\Customer\CustomerRegisteredEvent;
use Src\Domain\Identity\Models\Customer;

class CustomerWasRegistered
{
    use Dispatchable;

    public function __construct(
        public readonly Customer $customer,
        public readonly CustomerRegisteredEvent $domainEvent,
    ) {}
}
