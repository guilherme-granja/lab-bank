<?php

namespace Src\Application\Accounts\Handlers;

use Illuminate\Support\Facades\DB;
use Src\Application\Accounts\DataObjects\OpenAccountData;
use Src\Domain\Accounts\Enums\AccountTypeEnum;
use Src\Domain\Accounts\Exceptions\CustomerInAccountAlreadyExistsException;
use Src\Domain\Accounts\Models\Account;
use Src\Domain\Accounts\Models\AccountBalance;
use Src\Infrastructure\Services\SequenceService;
use Throwable;

class OpenAccountHandler
{
    public function __construct(
        protected SequenceService $sequenceService,
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(OpenAccountData $openAccountData): void
    {
        throw_if(
            condition: Account::where('customer_id', $openAccountData->customerId)->exists(),
            exception: CustomerInAccountAlreadyExistsException::class,
        );

        DB::connection('accounts')->transaction(function () use ($openAccountData) {
            $uniqueAccountNumber = $this->sequenceService->generateAccountNumberSequence();

            $account = Account::register($openAccountData->customerId, AccountTypeEnum::Checking, $uniqueAccountNumber);
            $accountBalance = AccountBalance::register($account);

            $account->save();
            $accountBalance->save();
        });
    }
}
