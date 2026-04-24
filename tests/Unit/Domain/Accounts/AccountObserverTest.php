<?php

use Illuminate\Support\Facades\Event;
use Src\Domain\Accounts\Enums\AccountTypeEnum;
use Src\Domain\Accounts\Models\Account;
use Src\Interfaces\Events\Account\AccountWasOpened;

describe('AccountObserver', function () {
    it('dispatches AccountWasOpened when an account is saved for the first time', function () {
        Event::fake([AccountWasOpened::class]);

        $account = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000001');
        $account->save();

        Event::assertDispatched(AccountWasOpened::class);
    });

    it('dispatches no business events when an account is saved with no domain events', function () {
        $account = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000001');
        $account->save();
        $account->pullDomainEvents();

        Event::fake([AccountWasOpened::class]);

        $account->save();

        Event::assertNotDispatched(AccountWasOpened::class);
    });
});
