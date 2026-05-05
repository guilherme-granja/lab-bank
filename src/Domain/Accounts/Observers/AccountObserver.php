<?php

namespace Src\Domain\Accounts\Observers;

use Src\Domain\Accounts\Events\Account\AccountOpenedEvent;
use Src\Domain\Accounts\Events\Account\FundsDepositedEvent;
use Src\Domain\Accounts\Models\Account;
use Src\Interfaces\Events\Account\AccountWasOpened;
use Src\Interfaces\Events\Account\FundsWereDeposited;

class AccountObserver
{
    public function created(Account $account): void
    {
        $this->dispatchEvent($account);
    }

    public function updated(Account $account): void
    {
        $this->dispatchEvent($account);
    }

    public function saved(Account $account): void
    {
        $this->dispatchEvent($account);
    }

    private function dispatchEvent(Account $account): void
    {
        foreach ($account->pullDomainEvents() as $domainEvent) {
            $event = match ($domainEvent::class) {
                AccountOpenedEvent::class => new AccountWasOpened($account, $domainEvent),
                FundsDepositedEvent::class => new FundsWereDeposited($account, $domainEvent),
                default => null,
            };

            if (! is_null($event)) {
                event($event);
            }
        }
    }
}
