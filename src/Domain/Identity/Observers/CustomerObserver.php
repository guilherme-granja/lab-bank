<?php

namespace Src\Domain\Identity\Observers;

use Src\Domain\Identity\Events\Customer\CustomerActivated;
use Src\Domain\Identity\Events\Customer\CustomerBlocked;
use Src\Domain\Identity\Events\Customer\CustomerRegistered;
use Src\Domain\Identity\Events\Customer\KycApproved;
use Src\Domain\Identity\Events\Customer\KycRejected;
use Src\Domain\Identity\Models\Customer;
use Src\Interfaces\Events\Identity\CustomerWasActivated;
use Src\Interfaces\Events\Identity\CustomerWasBlocked;
use Src\Interfaces\Events\Identity\CustomerWasRegistered;
use Src\Interfaces\Events\Identity\KycWasApproved;
use Src\Interfaces\Events\Identity\KycWasRejected;

class CustomerObserver
{
    public function saved(Customer $customer): void
    {
        foreach ($customer->pullDomainEvents() as $domainEvent) {
            $event = match ($domainEvent::class) {
                CustomerRegistered::class => new CustomerWasRegistered($customer, $domainEvent),
                CustomerBlocked::class => new CustomerWasBlocked($customer, $domainEvent),
                CustomerActivated::class => new CustomerWasActivated($customer, $domainEvent),
                KycApproved::class => new KycWasApproved($customer, $domainEvent),
                KycRejected::class => new KycWasRejected($customer, $domainEvent),
                default => null,
            };

            if (!is_null($event)) {
                event($event);
            }
        }
    }
}
