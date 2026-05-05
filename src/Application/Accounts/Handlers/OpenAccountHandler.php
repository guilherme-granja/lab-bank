<?php

namespace Src\Application\Accounts\Handlers;

use Illuminate\Support\Facades\DB;
use Src\Application\Accounts\DataObjects\OpenAccountData;
use Src\Domain\Accounts\Enums\AccountTypeEnum;
use Src\Domain\Accounts\Exceptions\CustomerInAccountAlreadyExistsException;
use Src\Domain\Accounts\Models\Account;
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
            condition: Account::existsForCustomer($openAccountData->customerId),
            exception: CustomerInAccountAlreadyExistsException::class,
        );

        DB::connection('accounts')->transaction(function () use ($openAccountData) {
            $uniqueAccountNumber = $this->sequenceService->generateAccountNumberSequence();

            $account = Account::create([
                'customer_id' => $openAccountData->customerId,
                'account_number' => $uniqueAccountNumber,
                'account_type' => AccountTypeEnum::Checking,
            ])->refresh();

            $account->balance()->create([
                'available_balance' => 0,
                'blocked_amount' => 0,
                'last_updated_at' => now(),
            ]);
        });
    }
}
