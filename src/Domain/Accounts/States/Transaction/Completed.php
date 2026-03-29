<?php

namespace Src\Domain\Accounts\States\Transaction;

use Src\Domain\Accounts\States\TransactionStatus;

class Completed extends TransactionStatus
{
    public static string $name = 'completed';
}
