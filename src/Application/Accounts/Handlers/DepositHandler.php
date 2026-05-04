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
use Src\Domain\Accounts\Models\LedgerEntry;
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
            $transaction = $this->setTransaction($account, $data);
            $ledgerEntry = $this->setLedgerEntry($account, $data, $transaction, $accountBalance);

            $accountBalance->available_balance += $data->amount;

            $transaction->save();
            $ledgerEntry->save();
            $accountBalance->save();

            $account->deposit($data->amount);
            $account->save();

            $transaction->complete();

            $transaction->save();
        });
    }

    private function setTransaction(Account $account, DepositData $data): Transaction
    {
        return Transaction::register(
            correlationId: $data->getCorrelationId(),
            amount: $data->amount,
            type: TransactionTypeEnum::Deposit,
            originAccountId: $account->id,
            description: $data->description,
        );
    }

    private function setLedgerEntry(Account $account, DepositData $data, Transaction $transaction, AccountBalance $accountBalance): LedgerEntry
    {
        return LedgerEntry::register(
            accountId: $account->id,
            type: LedgerEntryTypeEnum::Credit,
            amount: $data->amount,
            balanceAfter: $accountBalance->available_balance + $data->amount,
            description: $data->description,
            category: LedgerEntryCategory::Deposit,
            transactionId: $transaction->id,
            correlationId: $data->getCorrelationId(),
        );
    }
}
