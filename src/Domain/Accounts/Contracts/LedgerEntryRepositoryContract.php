<?php

namespace Src\Domain\Accounts\Contracts;

use Src\Domain\Accounts\Models\LedgerEntry;

interface LedgerEntryRepositoryContract
{
    public function save(LedgerEntry $ledgerEntry): void;
}
