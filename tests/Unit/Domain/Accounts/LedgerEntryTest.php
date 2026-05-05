<?php

use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Src\Domain\Accounts\Enums\LedgerEntryCategory;
use Src\Domain\Accounts\Enums\LedgerEntryTypeEnum;
use Src\Domain\Accounts\Models\LedgerEntry;

describe('LedgerEntry::create()', function () {
    it('sets all provided fields', function () {
        $accountId = AccountFactory::new()->create()->id;
        $transactionId = Str::uuid()->toString();
        $correlationId = Str::uuid()->toString();

        $entry = LedgerEntry::create([
            'account_id' => $accountId,
            'type' => LedgerEntryTypeEnum::Credit,
            'amount' => 500,
            'balance_after' => 1500,
            'description' => 'deposit',
            'category' => LedgerEntryCategory::Deposit,
            'transaction_id' => $transactionId,
            'correlation_id' => $correlationId,
            'occurred_at' => now(),
        ]);

        expect($entry->account_id)->toBe($accountId)
            ->and($entry->type)->toBe(LedgerEntryTypeEnum::Credit)
            ->and($entry->amount)->toBe(500)
            ->and($entry->balance_after)->toBe(1500)
            ->and($entry->description)->toBe('deposit')
            ->and($entry->category)->toBe(LedgerEntryCategory::Deposit)
            ->and($entry->transaction_id)->toBe($transactionId)
            ->and($entry->correlation_id)->toBe($correlationId)
            ->and($entry->occurred_at)->not->toBeNull()
            ->and($entry->id)->toMatch('/^[0-9a-f-]{36}$/');
    });

    it('belongs to an Account', function () {
        $entry = new LedgerEntry;
        expect($entry->account())->toBeInstanceOf(BelongsTo::class);
    });
});
