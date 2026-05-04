<?php

namespace Src\Interfaces\Events\Account;

use Illuminate\Foundation\Events\Dispatchable;
use Src\Domain\Accounts\Events\Account\FundsDepositedEvent;
use Src\Domain\Accounts\Models\Account;

class FundsWereDeposited
{
    use Dispatchable;

    public function __construct(
        public readonly Account $account,
        public readonly FundsDepositedEvent $domainEvent,
    ) {}
}
