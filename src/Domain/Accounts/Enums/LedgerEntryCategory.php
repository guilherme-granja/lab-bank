<?php

namespace Src\Domain\Accounts\Enums;

use Src\Shared\Traits\Enum\ToArray;

enum LedgerEntryCategory: string
{
    use ToArray;

    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
    case CardPayment = 'card_payment';
    case InvestmentBuy = 'investment_buy';
    case InvestmentSell = 'investment_sell';
    case Fee = 'fee';
    case Reverse = 'reverse';
    case Yeild = 'yeild';
}
