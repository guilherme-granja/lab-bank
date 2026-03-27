<?php

namespace Src\Domain\Identity\Events\Customer;

use Src\Domain\Identity\Models\Customer;
use Src\Shared\Events\DomainEvent;

class CustomerBlockedEvent extends DomainEvent
{
    public function __construct(
        protected Customer $customer,
        protected string $reason,
    ) {
        parent::__construct(
            $this->customer->id,
            class_basename($this->customer::class)
        );
    }

    public function toPayload(): array
    {
        return [
            'reason' => $this->reason,
            'kyc_status' => $this->customer->kyc_status::getMorphClass(),
            'status' => $this->customer->status::getMorphClass(),
        ];
    }
}
