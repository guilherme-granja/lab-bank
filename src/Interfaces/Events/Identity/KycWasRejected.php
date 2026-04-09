<?php

namespace Src\Interfaces\Events\Identity;

use Illuminate\Foundation\Events\Dispatchable;
use Src\Domain\Identity\Events\Customer\KycRejectedEvent;
use Src\Domain\Identity\Models\Customer;

class KycWasRejected
{
    use Dispatchable;

    public function __construct(
        public readonly Customer $customer,
        public readonly KycRejectedEvent $domainEvent,
    ) {}
}
