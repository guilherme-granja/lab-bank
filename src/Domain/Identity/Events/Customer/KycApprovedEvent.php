<?php

namespace Src\Domain\Identity\Events\Customer;

use Illuminate\Support\Carbon;
use Src\Domain\Identity\Models\Customer;
use Src\Shared\Events\DomainEvent;

class KycApprovedEvent extends DomainEvent
{
    public function __construct(protected Customer $customer)
    {
        parent::__construct(
            $this->customer->id,
            class_basename($this->customer::class)
        );
    }

    public function toPayload(): array
    {
        return [
            'email' => $this->customer->email,
            'approved_at' => $this->occurredAt,
        ];
    }
}
