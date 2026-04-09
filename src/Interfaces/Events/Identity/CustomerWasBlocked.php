<?php

namespace Src\Interfaces\Events\Identity;

use Illuminate\Foundation\Events\Dispatchable;
use Src\Domain\Identity\Events\Customer\CustomerBlockedEvent;
use Src\Domain\Identity\Models\Customer;

class CustomerWasBlocked
{
    use Dispatchable;

    public function __construct(
        public readonly Customer $customer,
        public readonly CustomerBlockedEvent $domainEvent,
    ) {}
}
