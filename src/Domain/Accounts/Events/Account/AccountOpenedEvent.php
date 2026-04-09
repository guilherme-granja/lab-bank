<?php

namespace Src\Domain\Accounts\Events\Account;

use Src\Domain\Accounts\Models\Account;
use Src\Shared\Events\DomainEvent;

class AccountOpenedEvent extends DomainEvent
{
    public function __construct(protected Account $account)
    {
        parent::__construct(
            $this->account->id,
            class_basename($this->account::class)
        );
    }

    public function toPayload(): array
    {
        return [
            'account_number' => $this->account->account_number,
            'branch' => $this->account->branch,
            'bank_code' => $this->account->bank_code,
            'account_type' => $this->account->account_type->value,
        ];
    }
}
