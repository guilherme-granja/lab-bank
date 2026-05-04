<?php

namespace Src\Domain\Accounts\Contracts;

use Src\Domain\Accounts\Models\Transaction;

interface TransactionRepositoryContract
{
    public function save(Transaction $transaction): void;
}
