<?php

use Illuminate\Support\Str;
use Src\Domain\Accounts\Enums\TransactionTypeEnum;
use Src\Domain\Accounts\Models\Transaction;
use Src\Domain\Accounts\States\Transaction\Completed;
use Src\Domain\Accounts\States\Transaction\Initiated;
use Src\Domain\Accounts\States\Transaction\Processing;

describe('Transaction::register()', function () {
    it('sets the provided fields and assigns a uuid', function () {
        $correlationId = (string) Str::uuid();
        $transaction = Transaction::register(
            correlationId: $correlationId,
            amount: 1500,
            type: TransactionTypeEnum::Deposit,
            originAccountId: 'origin-uuid',
            destinationAccountId: 'destination-uuid',
            description: 'salary',
        );

        expect($transaction->correlation_id)->toBe($correlationId);
        expect($transaction->amount)->toBe(1500);
        expect($transaction->type)->toBe(TransactionTypeEnum::Deposit);
        expect($transaction->origin_account_id)->toBe('origin-uuid');
        expect($transaction->destination_account_id)->toBe('destination-uuid');
        expect($transaction->description)->toBe('salary');
        expect($transaction->id)->toMatch('/^[0-9a-f-]{36}$/');
    });

    it('defaults status to Initiated', function () {
        $transaction = Transaction::register((string) Str::uuid(), 100, TransactionTypeEnum::Deposit);
        expect($transaction->status)->toBeInstanceOf(Initiated::class);
    });
});

describe('Transaction::process()', function () {
    it('transitions status from Initiated to Processing', function () {
        $transaction = Transaction::register((string) Str::uuid(), 100, TransactionTypeEnum::Deposit);
        $transaction->save();

        $transaction->process();

        expect($transaction->status)->toBeInstanceOf(Processing::class);
    });
});

describe('Transaction::complete()', function () {
    it('transitions status to Completed and sets completed_at', function () {
        $transaction = Transaction::register((string) Str::uuid(), 100, TransactionTypeEnum::Deposit);
        $transaction->save();

        $transaction->complete();

        expect($transaction->status)->toBeInstanceOf(Completed::class);
        expect($transaction->completed_at)->not->toBeNull();
    });
});
