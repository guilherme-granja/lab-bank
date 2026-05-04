<?php

namespace Src\Domain\Accounts\Contracts;

use Src\Domain\Accounts\Models\AccountBalance;

interface AccountBalanceRepositoryContract
{
    public function save(AccountBalance $accountBalance): void;

    public function findByAccoundIdForUpdate(string $accountId): ?AccountBalance;

    public function updateAvailableAmount(AccountBalance $accountBalance, int $amount): AccountBalance;
}
