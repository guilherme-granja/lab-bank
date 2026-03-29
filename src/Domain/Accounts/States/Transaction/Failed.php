<?php

namespace Src\Domain\Accounts\States\Transaction;

use Src\Domain\Accounts\States\TransactionStatus;

class Failed extends TransactionStatus
{
    public static string $name = 'failed';
}
