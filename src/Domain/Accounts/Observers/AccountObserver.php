<?php

namespace Src\Domain\Accounts\Observers;

use Src\Domain\Accounts\Events\Account\AccountOpenedEvent;
use Src\Domain\Accounts\Models\Account;
use Src\Interfaces\Events\Account\AccountWasOpened;

class AccountObserver
{
    public function saved(Account $account): void
    {
        foreach ($account->pullDomainEvents() as $domainEvent) {
            $event = match ($domainEvent::class) {
                AccountOpenedEvent::class => new AccountWasOpened($account, $domainEvent),
                default => null,
            };

            if (!is_null($event)) {
                event($event);
            }
        }
    }
}
