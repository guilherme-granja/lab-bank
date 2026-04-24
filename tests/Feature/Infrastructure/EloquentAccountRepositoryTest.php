<?php

use Illuminate\Support\Facades\Event;
use Src\Domain\Accounts\Enums\AccountTypeEnum;
use Src\Domain\Accounts\Models\Account;
use Src\Domain\Accounts\Models\AccountBalance;
use Src\Infrastructure\Persistence\Accounts\EloquentAccountBalanceRepository;
use Src\Infrastructure\Persistence\Accounts\EloquentAccountRepository;

beforeEach(function () {
    Event::fake();
    $this->accountRepository = new EloquentAccountRepository;
    $this->accountBalanceRepository = new EloquentAccountBalanceRepository;
});

describe('EloquentAccountRepository', function () {
    it('saves an account to the database', function () {
        $account = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000001');

        $this->accountRepository->save($account);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'customer_id' => 'customer-uuid',
            'account_number' => '1000000001',
        ], 'accounts');
    });

    it('existsByCustomerId returns true when the customer has an account', function () {
        $account = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000001');
        $this->accountRepository->save($account);

        expect($this->accountRepository->existsByCustomerId('customer-uuid'))->toBeTrue();
    });

    it('existsByCustomerId returns false when the customer has no account', function () {
        expect($this->accountRepository->existsByCustomerId('non-existent-customer'))->toBeFalse();
    });
});

describe('EloquentAccountBalanceRepository', function () {
    it('saves an account balance to the database', function () {
        $account = Account::register('customer-uuid', AccountTypeEnum::Checking, '1000000001');
        $this->accountRepository->save($account);

        $balance = AccountBalance::register($account);
        $this->accountBalanceRepository->save($balance);

        $this->assertDatabaseHas('account_balances', [
            'account_id' => $account->id,
            'available_balance' => 0,
            'blocked_amount' => 0,
        ], 'accounts');
    });
});
