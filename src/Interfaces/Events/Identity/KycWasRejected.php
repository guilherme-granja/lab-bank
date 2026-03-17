<?php

namespace Src\Interfaces\Events\Identity;

use Illuminate\Foundation\Events\Dispatchable;
use Src\Domain\Identity\Events\Customer\KycRejected;
use Src\Domain\Identity\Models\Customer;

class KycWasRejected
{
    use Dispatchable;

    public function __construct(
        public readonly Customer $customer,
        public readonly KycRejected $domainEvent,
    ) {}
}
