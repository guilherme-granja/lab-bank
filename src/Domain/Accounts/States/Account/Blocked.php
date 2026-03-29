<?php

namespace Src\Domain\Accounts\States\Account;

use Src\Domain\Accounts\States\AccountStatus;

class Blocked extends AccountStatus
{
    public static string $name = 'blocked';
}
