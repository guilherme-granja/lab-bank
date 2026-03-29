<?php

namespace Src\Domain\Accounts\States\Account;

use Src\Domain\Accounts\States\AccountStatus;

class Closed extends AccountStatus
{
    public static string $name = 'closed';
}
