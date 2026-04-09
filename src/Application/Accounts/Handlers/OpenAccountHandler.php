<?php

namespace Src\Application\Accounts\Handlers;

use Illuminate\Support\Facades\DB;
use Src\Application\Accounts\DataObjects\OpenAccountData;
use Src\Domain\Accounts\Contracts\AccountBalanceRepositoryContract;
use Src\Domain\Accounts\Contracts\AccountRepositoryContract;
use Src\Domain\Accounts\Enums\AccountTypeEnum;
use Src\Domain\Accounts\Exceptions\CustomerInAccountAlreadyExistsException;
use Src\Domain\Accounts\Models\Account;
use Src\Domain\Accounts\Models\AccountBalance;
use Src\Infrastructure\Services\SequenceService;
use Throwable;

class OpenAccountHandler
{
    public function __construct(
        protected AccountRepositoryContract $accountRepository,
        protected AccountBalanceRepositoryContract $accountBalanceRepository,
        protected SequenceService $sequenceService,
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(OpenAccountData $openAccountData): void
    {
        throw_if(
            condition: $this->accountRepository->existsByCustomerId($openAccountData->customerId),
            exception: CustomerInAccountAlreadyExistsException::class,
        );

        DB::connection('accounts')->transaction(function () use ($openAccountData) {
            $uniqueAccountNumber = $this->sequenceService->generateAccountNumberSequence();

            $account = Account::register($openAccountData->customerId, AccountTypeEnum::Checking, $uniqueAccountNumber);
            $accountBalance = AccountBalance::register($account);

            $this->accountRepository->save($account);
            $this->accountBalanceRepository->save($accountBalance);
        });
    }
}
