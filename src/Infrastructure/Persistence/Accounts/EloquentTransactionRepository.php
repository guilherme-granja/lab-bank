<?php

namespace Src\Infrastructure\Persistence\Accounts;

use Src\Domain\Accounts\Contracts\TransactionRepositoryContract;
use Src\Domain\Accounts\Models\Transaction;

class EloquentTransactionRepository implements TransactionRepositoryContract
{
    public function save(Transaction $transaction): void
    {
        $transaction->save();
    }
}
