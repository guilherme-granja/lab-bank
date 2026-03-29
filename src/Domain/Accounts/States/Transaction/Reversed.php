<?php

namespace Src\Domain\Accounts\States\Transaction;

use Src\Domain\Accounts\States\TransactionStatus;

class Reversed extends TransactionStatus
{
    public static string $name = 'reversed';
}
