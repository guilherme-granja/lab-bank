<?php

use Database\Factories\AccountFactory;
use Illuminate\Support\Facades\Event;
use Src\Domain\Accounts\Enums\AccountTypeEnum;
use Src\Domain\Accounts\Events\Account\AccountOpenedEvent;

beforeEach(function () {
    Event::fake();
});

describe('AccountOpenedEvent', function () {
    it('toPayload returns the expected fields', function () {
        $account = AccountFactory::new()->create();
        $account->pullDomainEvents();
        $event = new AccountOpenedEvent($account);

        $payload = $event->toPayload();

        expect($payload)->toHaveKeys(['account_number', 'branch', 'bank_code', 'account_type'])
            ->and($payload['account_number'])->toBe('1000000123')
            ->and($payload['account_type'])->toBe(AccountTypeEnum::Checking->value);
    });

    it('aggregateId is the account id', function () {
        $account = AccountFactory::new()->create();
        $account->pullDomainEvents();
        $event = new AccountOpenedEvent($account);

        expect($event->aggregateId)->toBe($account->id);
    });

    it('aggregateType is Account', function () {
        $account = AccountFactory::new()->create();
        $account->pullDomainEvents();
        $event = new AccountOpenedEvent($account);

        expect($event->aggregateType)->toBe('Account');
    });
});
