<?php

namespace Src\Infrastructure\Persistence\Accounts;

use Src\Domain\Accounts\Contracts\AccountBalanceRepositoryContract;
use Src\Domain\Accounts\Models\AccountBalance;

class EloquentAccountBalanceRepository implements AccountBalanceRepositoryContract
{
    public function save(AccountBalance $accountBalance): void
    {
        $accountBalance->save();
    }
}
