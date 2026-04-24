<?php

namespace Src\Domain\Identity\Observers;

use Src\Domain\Identity\Events\Customer\CustomerActivatedEvent;
use Src\Domain\Identity\Events\Customer\CustomerBlockedEvent;
use Src\Domain\Identity\Events\Customer\CustomerRegisteredEvent;
use Src\Domain\Identity\Events\Customer\KycApprovedEvent;
use Src\Domain\Identity\Events\Customer\KycRejectedEvent;
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
                CustomerRegisteredEvent::class => new CustomerWasRegistered($customer, $domainEvent),
                CustomerBlockedEvent::class => new CustomerWasBlocked($customer, $domainEvent),
                CustomerActivatedEvent::class => new CustomerWasActivated($customer, $domainEvent),
                KycApprovedEvent::class => new KycWasApproved($customer, $domainEvent),
                KycRejectedEvent::class => new KycWasRejected($customer, $domainEvent),
                default => null,
            };

            if (! is_null($event)) {
                event($event);
            }
        }
    }
}
