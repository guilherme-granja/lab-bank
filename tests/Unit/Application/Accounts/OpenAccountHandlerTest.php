<?php

use Illuminate\Support\Facades\Event;
use Src\Application\Accounts\DataObjects\OpenAccountData;
use Src\Application\Accounts\Handlers\OpenAccountHandler;
use Src\Domain\Accounts\Enums\AccountTypeEnum;
use Src\Domain\Accounts\Exceptions\CustomerInAccountAlreadyExistsException;
use Src\Domain\Accounts\Models\Account;
use Src\Domain\Accounts\Models\AccountBalance;
use Src\Infrastructure\Services\SequenceService;

use function Pest\Laravel\mock;

beforeEach(function () {
    Event::fake();

    $this->sequenceService = mock(SequenceService::class);
    $this->handler = new OpenAccountHandler($this->sequenceService);
});

describe('OpenAccountHandler', function () {
    it('throws CustomerInAccountAlreadyExistsException when customer already has an account', function () {
        $customerId = '11111111-1111-1111-1111-111111111111';
        Account::register($customerId, AccountTypeEnum::Checking, '1000000000')->save();

        expect(fn () => ($this->handler)(new OpenAccountData(customerId: $customerId)))
            ->toThrow(CustomerInAccountAlreadyExistsException::class);
    });

    it('opens a checking account and creates a zero balance for the customer', function () {
        $customerId = '22222222-2222-2222-2222-222222222222';
        $this->sequenceService->shouldReceive('generateAccountNumberSequence')->once()->andReturn('1000000001');

        ($this->handler)(new OpenAccountData(customerId: $customerId));

        $account = Account::where('customer_id', $customerId)->first();
        expect($account)->not->toBeNull();
        expect($account->account_type)->toBe(AccountTypeEnum::Checking);

        $balance = AccountBalance::where('account_id', $account->id)->first();
        expect($balance)->not->toBeNull();
        expect($balance->available_balance)->toBe(0);
        expect($balance->blocked_amount)->toBe(0);
    });

    it('uses the generated sequence number as the account number', function () {
        $customerId = '33333333-3333-3333-3333-333333333333';
        $this->sequenceService->shouldReceive('generateAccountNumberSequence')->once()->andReturn('9999999999');

        ($this->handler)(new OpenAccountData(customerId: $customerId));

        expect(Account::where('customer_id', $customerId)->value('account_number'))->toBe('9999999999');
    });

    it('does not save when customer already has an account', function () {
        $customerId = '44444444-4444-4444-4444-444444444444';
        Account::register($customerId, AccountTypeEnum::Checking, '1000000000')->save();
        $countBefore = Account::where('customer_id', $customerId)->count();

        expect(fn () => ($this->handler)(new OpenAccountData(customerId: $customerId)))
            ->toThrow(CustomerInAccountAlreadyExistsException::class);

        expect(Account::where('customer_id', $customerId)->count())->toBe($countBefore);
    });

    it('does not generate a sequence number when customer already has an account', function () {
        $customerId = '55555555-5555-5555-5555-555555555555';
        Account::register($customerId, AccountTypeEnum::Checking, '1000000000')->save();
        $this->sequenceService->shouldNotReceive('generateAccountNumberSequence');

        expect(fn () => ($this->handler)(new OpenAccountData(customerId: $customerId)))
            ->toThrow(CustomerInAccountAlreadyExistsException::class);
    });
});
