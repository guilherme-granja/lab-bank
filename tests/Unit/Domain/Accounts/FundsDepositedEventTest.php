<?php

use Src\Domain\Accounts\Enums\AccountTypeEnum;
use Src\Domain\Accounts\Events\Account\FundsDepositedEvent;
use Src\Domain\Accounts\Models\Account;

describe('FundsDepositedEvent', function () {
    it('toPayload returns the expected fields', function () {
        $account = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000777');
        $account->branch = '0001';
        $account->bank_code = '000';
        $account->pullDomainEvents();

        $event = new FundsDepositedEvent($account, 2500);

        $payload = $event->toPayload();

        expect($payload)->toHaveKeys(['account_number', 'branch', 'bank_code', 'account_type', 'amount']);
        expect($payload['account_number'])->toBe('1000000777');
        expect($payload['branch'])->toBe('0001');
        expect($payload['bank_code'])->toBe('000');
        expect($payload['account_type'])->toBe(AccountTypeEnum::Checking->value);
        expect($payload['amount'])->toBe(2500);
    });

    it('aggregateId is the account id', function () {
        $account = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000777');
        $event = new FundsDepositedEvent($account, 100);

        expect($event->aggregateId)->toBe($account->id);
    });

    it('aggregateType is Account', function () {
        $account = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000777');
        $event = new FundsDepositedEvent($account, 100);

        expect($event->aggregateType)->toBe('Account');
    });
});
