<?php

namespace Src\Domain\Accounts\Enums;

use Src\Shared\Traits\Enum\ToArray;

enum TransactionType: string
{
    use ToArray;

    case Transfer = 'transfer';
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case CardPayment = 'card_payment';
    case Investment = 'investment';
    case Reversal = 'reversal';
}
