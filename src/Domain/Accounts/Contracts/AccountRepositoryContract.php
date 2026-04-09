<?php

namespace Src\Domain\Accounts\Contracts;

use Src\Domain\Accounts\Models\Account;

interface AccountRepositoryContract
{
    public function save(Account $account): void;

    public function existsByCustomerId(string $customerId): bool;
}
