<?php

namespace Src\Domain\Identity\Events\Customer;

use Src\Domain\Identity\Models\Customer;
use Src\Shared\Events\DomainEvent;

class CustomerRegisteredEvent extends DomainEvent
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
            'full_name' => $this->customer->full_name,
            'cpf' => $this->customer->cpf,
            'email' => $this->customer->email,
            'phone' => $this->customer->phone,
            'birth_date' => $this->customer->birth_date,
            'kyc_status' => $this->customer->kyc_status::getMorphClass(),
            'status' => $this->customer->status::getMorphClass(),
        ];
    }
}
