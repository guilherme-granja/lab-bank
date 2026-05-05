<?php

namespace Src\Application\Accounts\Handlers;

use Illuminate\Support\Facades\DB;
use Src\Application\Accounts\DataObjects\DepositData;
use Src\Domain\Accounts\Enums\LedgerEntryCategory;
use Src\Domain\Accounts\Enums\LedgerEntryTypeEnum;
use Src\Domain\Accounts\Enums\TransactionTypeEnum;
use Src\Domain\Accounts\Exceptions\AccountNotActiveException;
use Src\Domain\Accounts\Exceptions\AccountNotFoundException;
use Src\Domain\Accounts\Models\Account;
use Src\Domain\Accounts\Models\AccountBalance;
use Src\Domain\Accounts\Models\Transaction;
use Throwable;

class DepositHandler
{
    /**
     * @throws Throwable
     */
    public function __invoke(DepositData $data): void
    {
        $account = Account::find($data->accountId);

        throw_if(
            condition: is_null($account),
            exception: AccountNotFoundException::class,
        );

        throw_if(
            condition: ! $account->canDeposit(),
            exception: AccountNotActiveException::class,
        );

        DB::connection('accounts')->transaction(function () use ($account, $data) {
            $accountBalance = AccountBalance::where('account_id', $account->id)
                ->lockForUpdate()
                ->first();

            $accountBalance->updateAvailableBalance($data->amount);
            $accountBalance->refresh();

            $transaction = Transaction::create([
                'correlation_id' => $data->getCorrelationId(),
                'amount' => $data->amount,
                'type' => TransactionTypeEnum::Deposit,
                'origin_account_id' => $account->id,
                'description' => $data->description,
            ]);

            $account->ledgerEntries()->create([
                'type' => LedgerEntryTypeEnum::Credit,
                'amount' => $data->amount,
                'balance_after' => $accountBalance->available_balance,
                'description' => $data->description,
                'category' => LedgerEntryCategory::Deposit,
                'transaction_id' => $transaction->id,
                'correlation_id' => $data->getCorrelationId(),
                'occurred_at' => now(),
            ]);

            $transaction->complete();

            $account->deposit($data->amount);
            $account->save();
        });
    }
}
