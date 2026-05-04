<?php

namespace Src\Application\Accounts\Handlers;

use Illuminate\Support\Facades\DB;
use Src\Application\Accounts\DataObjects\DepositData;
use Src\Domain\Accounts\Contracts\AccountBalanceRepositoryContract;
use Src\Domain\Accounts\Contracts\AccountRepositoryContract;
use Src\Domain\Accounts\Contracts\LedgerEntryRepositoryContract;
use Src\Domain\Accounts\Contracts\TransactionRepositoryContract;
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
    public function __construct(
        protected AccountRepositoryContract $accountRepository,
        protected TransactionRepositoryContract $transactionRepository,
        protected LedgerEntryRepositoryContract $ledgerEntryRepository,
        protected AccountBalanceRepositoryContract $accountBalanceRepository,
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(DepositData $data): void
    {
        $account = $this->accountRepository->findById($data->accountId);

        throw_if(
            condition: is_null($account),
            exception: AccountNotFoundException::class,
        );

        throw_if(
            condition: ! $account->canDeposit(),
            exception: AccountNotActiveException::class,
        );

        DB::connection('accounts')->transaction(function () use ($account, $data) {
            $accountBalance = $this->accountBalanceRepository->findByAccoundIdForUpdate($account->id);
            $transaction = $this->setTransaction($account, $data);
            $ledgerEntry = $this->setLedgerEntry($account, $data, $transaction, $accountBalance);

            $updatedAccountBalance = $this->accountBalanceRepository->updateAvailableAmount($accountBalance, $data->amount);

            $this->transactionRepository->save($transaction);
            $this->ledgerEntryRepository->save($ledgerEntry);
            $this->accountBalanceRepository->save($updatedAccountBalance);

            $account->deposit($data->amount);
            $this->accountRepository->save($account);

            $transaction->complete();

            $this->transactionRepository->save($transaction);
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
