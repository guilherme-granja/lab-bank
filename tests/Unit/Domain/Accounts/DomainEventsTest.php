<?php

use Illuminate\Support\Facades\Event;
use Src\Domain\Accounts\Enums\AccountTypeEnum;
use Src\Domain\Accounts\Events\Account\AccountOpenedEvent;
use Src\Domain\Accounts\Models\Account;

beforeEach(function () {
    Event::fake();
});

describe('AccountOpenedEvent', function () {
    it('toPayload returns the expected fields', function () {
        $account = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000001');
        $account->pullDomainEvents();
        $event = new AccountOpenedEvent($account);

        $payload = $event->toPayload();

        expect($payload)->toHaveKeys(['account_number', 'branch', 'bank_code', 'account_type']);
        expect($payload['account_number'])->toBe('1000000001');
        expect($payload['account_type'])->toBe(AccountTypeEnum::Checking->value);
    });

    it('aggregateId is the account id', function () {
        $account = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000001');
        $account->pullDomainEvents();
        $event = new AccountOpenedEvent($account);

        expect($event->aggregateId)->toBe($account->id);
    });

    it('aggregateType is Account', function () {
        $account = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000001');
        $account->pullDomainEvents();
        $event = new AccountOpenedEvent($account);

        expect($event->aggregateType)->toBe('Account');
    });
});
