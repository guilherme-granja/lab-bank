<?php

namespace Src\Interfaces\Events\Identity;

use Illuminate\Foundation\Events\Dispatchable;
use Src\Domain\Identity\Events\Customer\KycApprovedEvent;
use Src\Domain\Identity\Models\Customer;

class KycWasApproved
{
    use Dispatchable;

    public function __construct(
        public readonly Customer         $customer,
        public readonly KycApprovedEvent $domainEvent,
    ) {}
}
