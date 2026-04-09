<?php

namespace Src\Infrastructure\Persistence\Accounts;

use Src\Domain\Accounts\Contracts\AccountRepositoryContract;
use Src\Domain\Accounts\Models\Account;

class EloquentAccountRepository implements AccountRepositoryContract
{

    public function save(Account $account): void
    {
        $account->save();
    }

    public function existsByCustomerId(string $customerId): bool
    {
        return Account::query()
            ->where('customer_id', $customerId)
            ->exists();
    }
}
