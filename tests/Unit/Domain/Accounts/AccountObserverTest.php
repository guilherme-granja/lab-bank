<?php

use Illuminate\Support\Facades\Event;
use Src\Domain\Accounts\Enums\AccountTypeEnum;
use Src\Domain\Accounts\Models\Account;
use Src\Interfaces\Events\Account\AccountWasOpened;

describe('AccountObserver', function () {
    it('dispatches AccountWasOpened when an account is saved for the first time', function () {
        Event::fake([AccountWasOpened::class]);

        $account = Account::create([
            'customer_id' => \Illuminate\Support\Str::uuid()->toString(),
            'account_number' => '1000000123',
            'account_type' => AccountTypeEnum::Checking,
        ]);

        $account->registerEvent();

        Event::assertDispatched(AccountWasOpened::class);
    });

    it('dispatches no business events when an account is saved with no domain events', function () {
        $account = Account::create([
            'customer_id' => \Illuminate\Support\Str::uuid()->toString(),
            'account_number' => '1000000123',
            'account_type' => AccountTypeEnum::Checking,
        ]);

        $account->registerEvent();

        $account->pullDomainEvents();

        Event::fake([AccountWasOpened::class]);

        Event::assertNotDispatched(AccountWasOpened::class);
    });
});
