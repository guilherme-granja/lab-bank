<?php

namespace Src\Domain\Accounts\States\Account;

use Src\Domain\Accounts\States\AccountStatus;

class Pending extends AccountStatus
{
    public static string $name = 'pending';
}
