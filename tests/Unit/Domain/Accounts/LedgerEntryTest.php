<?php

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Src\Domain\Accounts\Enums\LedgerEntryCategory;
use Src\Domain\Accounts\Enums\LedgerEntryTypeEnum;
use Src\Domain\Accounts\Models\LedgerEntry;

describe('LedgerEntry::register()', function () {
    it('sets all provided fields', function () {
        $accountId = (string) Str::uuid();
        $transactionId = (string) Str::uuid();
        $correlationId = (string) Str::uuid();

        $entry = LedgerEntry::register(
            accountId: $accountId,
            type: LedgerEntryTypeEnum::Credit,
            amount: 500,
            balanceAfter: 1500,
            description: 'deposit',
            category: LedgerEntryCategory::Deposit,
            transactionId: $transactionId,
            correlationId: $correlationId,
        );

        expect($entry->account_id)->toBe($accountId);
        expect($entry->type)->toBe(LedgerEntryTypeEnum::Credit);
        expect($entry->amount)->toBe(500);
        expect($entry->balance_after)->toBe(1500);
        expect($entry->description)->toBe('deposit');
        expect($entry->category)->toBe(LedgerEntryCategory::Deposit);
        expect($entry->transaction_id)->toBe($transactionId);
        expect($entry->correlation_id)->toBe($correlationId);
        expect($entry->occurred_at)->not->toBeNull();
        expect($entry->id)->toMatch('/^[0-9a-f-]{36}$/');
    });

    it('belongs to an Account', function () {
        $entry = new LedgerEntry;
        expect($entry->account())->toBeInstanceOf(BelongsTo::class);
    });
});
