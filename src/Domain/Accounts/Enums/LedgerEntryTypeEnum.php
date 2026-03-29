<?php

namespace Src\Domain\Accounts\Enums;

use Src\Shared\Traits\Enum\ToArray;

enum LedgerEntryTypeEnum: string
{
    use ToArray;

    case Credit = 'credit';
    case Debit = 'debit';
}
