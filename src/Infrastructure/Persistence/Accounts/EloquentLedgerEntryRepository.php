<?php

namespace Src\Infrastructure\Persistence\Accounts;

use Src\Domain\Accounts\Contracts\LedgerEntryRepositoryContract;
use Src\Domain\Accounts\Models\LedgerEntry;

class EloquentLedgerEntryRepository implements LedgerEntryRepositoryContract
{
    public function save(LedgerEntry $ledgerEntry): void
    {
        $ledgerEntry->save();
    }
}
