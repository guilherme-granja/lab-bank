<?php

namespace Src\Domain\Accounts\Contracts;

use Src\Domain\Accounts\Models\AccountBalance;

interface AccountBalanceRepositoryContract
{
    public function save(AccountBalance $accountBalance): void;
}
