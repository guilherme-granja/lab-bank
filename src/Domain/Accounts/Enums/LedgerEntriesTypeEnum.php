<?php

namespace Src\Domain\Accounts\Enums;

use Src\Shared\Traits\Enum\ToArray;

enum LedgerEntriesTypeEnum: string
{
    use ToArray;

    case Credit = 'credit';
    case Debit = 'debit';
}
