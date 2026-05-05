<?php

use Illuminate\Support\Str;
use Src\Domain\Accounts\Enums\TransactionTypeEnum;
use Src\Domain\Accounts\Models\Transaction;
use Src\Domain\Accounts\States\Transaction\Completed;
use Src\Domain\Accounts\States\Transaction\Initiated;
use Src\Domain\Accounts\States\Transaction\Processing;

describe('Transaction::create()', function () {
    it('sets the provided fields and assigns a uuid', function () {
        $correlationId = Str::uuid()->toString();
        $transaction = Transaction::create([
            'correlation_id' => $correlationId,
            'amount' => 1500,
            'type' => TransactionTypeEnum::Deposit,
            'origin_account_id' => 'origin-uuid',
            'destination_account_id' => 'destination-uuid',
            'description' => 'salary',
        ])->refresh();

        expect($transaction->correlation_id)->toBe($correlationId)
            ->and($transaction->amount)->toBe(1500)
            ->and($transaction->type)->toBe(TransactionTypeEnum::Deposit)
            ->and($transaction->origin_account_id)->toBe('origin-uuid')
            ->and($transaction->destination_account_id)->toBe('destination-uuid')
            ->and($transaction->description)->toBe('salary')
            ->and($transaction->id)->toMatch('/^[0-9a-f-]{36}$/');
    });

    it('defaults status to Initiated', function () {
        $transaction = Transaction::create([
            'correlation_id' => Str::uuid()->toString(),
            'amount' => 100,
            'type' => TransactionTypeEnum::Deposit,
        ])->refresh();

        expect($transaction->status)->toBeInstanceOf(Initiated::class);
    });
});

describe('Transaction::process()', function () {
    it('transitions status from Initiated to Processing', function () {
        $transaction = Transaction::create([
            'correlation_id' => Str::uuid()->toString(),
            'amount' => 100,
            'type' => TransactionTypeEnum::Deposit,
        ])->refresh();

        $transaction->process();

        expect($transaction->status)->toBeInstanceOf(Processing::class);
    });
});

describe('Transaction::complete()', function () {
    it('transitions status to Completed and sets completed_at', function () {
        $transaction = Transaction::create([
            'correlation_id' => Str::uuid()->toString(),
            'amount' => 100,
            'type' => TransactionTypeEnum::Deposit,
        ]);

        $transaction->complete();

        expect($transaction->status)->toBeInstanceOf(Completed::class)
            ->and($transaction->completed_at)->not->toBeNull();
    });
});
