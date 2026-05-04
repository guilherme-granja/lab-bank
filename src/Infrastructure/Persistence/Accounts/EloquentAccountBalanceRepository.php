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

    public function findByAccoundIdForUpdate(string $accountId): ?AccountBalance
    {
        return AccountBalance::query()
            ->where('account_id', $accountId)
            ->lockForUpdate()
            ->first();
    }

    public function updateAvailableAmount(AccountBalance $accountBalance, int $amount): AccountBalance
    {
        $accountBalance->available_balance += $amount;

        return $accountBalance;
    }
}
