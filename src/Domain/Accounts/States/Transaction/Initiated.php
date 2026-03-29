<?php

namespace Src\Domain\Accounts\States\Transaction;

use Src\Domain\Accounts\States\TransactionStatus;

class Initiated extends TransactionStatus
{
    public static string $name = 'initiated';
}
