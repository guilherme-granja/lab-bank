<?php

namespace Src\Domain\Accounts\States\Transaction;

use Src\Domain\Accounts\States\TransactionStatus;

class Processing extends TransactionStatus
{
    public static string $name = 'processing';
}
