<?php

namespace Src\Infrastructure\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

class SequenceService
{
    /**
     * @throws Throwable
     */
    public function generateAccountNumberSequence(): string
    {
        $sequence = DB::connection('accounts')
            ->table('sequences')
            ->where('name', 'account_number')
            ->lockForUpdate()
            ->first();

        $next = $sequence->last_value + 1;

        DB::connection('accounts')
            ->table('sequences')
            ->where('name', 'account_number')
            ->update(['last_value' => $next]);

        return (string) $next;
    }
}
