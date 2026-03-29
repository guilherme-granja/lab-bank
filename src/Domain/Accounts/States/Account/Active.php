<?php

namespace Src\Domain\Accounts\States\Account;

use Src\Domain\Accounts\States\AccountStatus;

class Active extends AccountStatus
{
    public static string $name = 'active';
}
