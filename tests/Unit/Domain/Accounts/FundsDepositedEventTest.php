<?php

use Database\Factories\AccountFactory;
use Src\Domain\Accounts\Enums\AccountTypeEnum;
use Src\Domain\Accounts\Events\Account\FundsDepositedEvent;

describe('FundsDepositedEvent', function () {
    it('toPayload returns the expected fields', function () {
        $account = AccountFactory::new()->create();
        $account->branch = '0001';
        $account->bank_code = '000';
        $account->pullDomainEvents();

        $event = new FundsDepositedEvent($account, 2500);

        $payload = $event->toPayload();

        expect($payload)->toHaveKeys(['account_number', 'branch', 'bank_code', 'account_type', 'amount'])
            ->and($payload['account_number'])->toBe('1000000123')
            ->and($payload['branch'])->toBe('0001')
            ->and($payload['bank_code'])->toBe('000')
            ->and($payload['account_type'])->toBe(AccountTypeEnum::Checking->value)
            ->and($payload['amount'])->toBe(2500);
    });

    it('aggregateId is the account id', function () {
        $account = AccountFactory::new()->create();
        $event = new FundsDepositedEvent($account, 100);

        expect($event->aggregateId)->toBe($account->id);
    });

    it('aggregateType is Account', function () {
        $account = AccountFactory::new()->create();
        $event = new FundsDepositedEvent($account, 100);

        expect($event->aggregateType)->toBe('Account');
    });
});
