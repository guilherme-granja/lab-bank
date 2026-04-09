<?php

namespace Src\Interfaces\Events\Account;

use Illuminate\Foundation\Events\Dispatchable;
use Src\Domain\Accounts\Events\Account\AccountOpenedEvent;
use Src\Domain\Accounts\Models\Account;

class AccountWasOpened
{
    use Dispatchable;

    public function __construct(
        public readonly Account $account,
        public readonly AccountOpenedEvent $domainEvent,
    ) {}
}
