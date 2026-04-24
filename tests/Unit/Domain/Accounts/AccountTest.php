<?php

use Illuminate\Support\Facades\Event;
use Src\Domain\Accounts\Enums\AccountTypeEnum;
use Src\Domain\Accounts\Events\Account\AccountOpenedEvent;
use Src\Domain\Accounts\Models\Account;
use Src\Domain\Accounts\Models\AccountBalance;

beforeEach(function () {
    Event::fake();
});

describe('Account::register()', function () {
    it('sets customer_id, account_type, and account_number', function () {
        $account = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000001');

        expect($account->customer_id)->toBe('customer-uuid')
            ->and($account->account_type)->toBe(AccountTypeEnum::Checking)
            ->and($account->account_number)->toBe('1000000001');
    });

    it('assigns a uuid to the id field', function () {
        $account = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000001');

        expect($account->id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });

    it('records an AccountOpenedEvent', function () {
        $account = Account::register('customer-uuid', AccountTypeEnum::Savings, '1000000002');

        $events = $account->pullDomainEvents();

        expect($events)->toHaveCount(1)
            ->and($events[0])->toBeInstanceOf(AccountOpenedEvent::class);
    });

    it('sets activated_at to the current timestamp', function () {
        $account = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000001');

        expect($account->activated_at)->not->toBeNull();
    });

    it('each registered account has a distinct uuid', function () {
        $a = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000001');
        $b = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000002');

        expect($a->id)->not->toBe($b->id);
    });
});

describe('AccountBalance::register()', function () {
    it('initialises available_balance and blocked_amount to zero', function () {
        $account = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000001');
        $balance = AccountBalance::register($account);

        expect($balance->available_balance)->toBe(0)
            ->and($balance->blocked_amount)->toBe(0);
    });

    it('links the balance to the given account', function () {
        $account = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000001');
        $balance = AccountBalance::register($account);

        expect($balance->account_id)->toBe($account->id);
    });

    it('sets last_updated_at on creation', function () {
        $account = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000001');
        $balance = AccountBalance::register($account);

        expect($balance->last_updated_at)->not->toBeNull();
    });
});
