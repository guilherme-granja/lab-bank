<?php

namespace Src\Domain\Identity\Events\Customer;

use Src\Domain\Identity\Models\Customer;
use Src\Shared\Events\DomainEvent;

class KycRejected extends DomainEvent
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
            'email' => $this->customer->email,
            'reason' => $this->reason,
        ];
    }
}
