<?php

use Illuminate\Support\Facades\Event;
use Src\Application\Accounts\DataObjects\OpenAccountData;
use Src\Application\Accounts\Handlers\OpenAccountHandler;
use Src\Domain\Accounts\Contracts\AccountBalanceRepositoryContract;
use Src\Domain\Accounts\Contracts\AccountRepositoryContract;
use Src\Domain\Accounts\Exceptions\CustomerInAccountAlreadyExistsException;
use Src\Domain\Accounts\Models\Account;
use Src\Domain\Accounts\Models\AccountBalance;
use Src\Infrastructure\Services\SequenceService;

use function Pest\Laravel\mock;

beforeEach(function () {
    Event::fake();

    $this->accountRepository = mock(AccountRepositoryContract::class);
    $this->accountBalanceRepository = mock(AccountBalanceRepositoryContract::class);
    $this->sequenceService = mock(SequenceService::class);

    $this->handler = new OpenAccountHandler(
        $this->accountRepository,
        $this->accountBalanceRepository,
        $this->sequenceService,
    );
});

describe('OpenAccountHandler', function () {
    it('throws CustomerInAccountAlreadyExistsException when customer already has an account', function () {
        $this->accountRepository->shouldReceive('existsByCustomerId')->once()->andReturn(true);

        expect(fn () => ($this->handler)(new OpenAccountData(customerId: 'customer-uuid')))
            ->toThrow(CustomerInAccountAlreadyExistsException::class);
    });

    it('opens a checking account and creates a zero balance for the customer', function () {
        $this->accountRepository->shouldReceive('existsByCustomerId')->once()->andReturn(false);
        $this->sequenceService->shouldReceive('generateAccountNumberSequence')->once()->andReturn('1000000001');
        $this->accountRepository->shouldReceive('save')->once()->with(Mockery::type(Account::class));
        $this->accountBalanceRepository->shouldReceive('save')->once()->with(Mockery::type(AccountBalance::class));

        ($this->handler)(new OpenAccountData(customerId: 'customer-uuid'));
    });

    it('uses the generated sequence number as the account number', function () {
        $this->accountRepository->shouldReceive('existsByCustomerId')->once()->andReturn(false);
        $this->sequenceService->shouldReceive('generateAccountNumberSequence')->once()->andReturn('9999999999');
        $this->accountRepository->shouldReceive('save')->once()
            ->with(Mockery::on(fn (Account $account) => $account->account_number === '9999999999'));
        $this->accountBalanceRepository->shouldReceive('save')->once();

        ($this->handler)(new OpenAccountData(customerId: 'customer-uuid'));
    });

    it('does not save when customer already has an account', function () {
        $this->accountRepository->shouldReceive('existsByCustomerId')->once()->andReturn(true);
        $this->accountRepository->shouldNotReceive('save');
        $this->accountBalanceRepository->shouldNotReceive('save');

        expect(fn () => ($this->handler)(new OpenAccountData(customerId: 'customer-uuid')))
            ->toThrow(CustomerInAccountAlreadyExistsException::class);
    });

    it('does not generate a sequence number when customer already has an account', function () {
        $this->accountRepository->shouldReceive('existsByCustomerId')->once()->andReturn(true);
        $this->sequenceService->shouldNotReceive('generateAccountNumberSequence');

        expect(fn () => ($this->handler)(new OpenAccountData(customerId: 'customer-uuid')))
            ->toThrow(CustomerInAccountAlreadyExistsException::class);
    });
});
