<?php

use Database\Factories\AccountFactory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Src\Domain\Accounts\Exceptions\AccountNotActiveException;
use Src\Domain\Accounts\Exceptions\AccountNotFoundException;
use Src\Domain\Accounts\Models\AccountBalance;
use Src\Domain\Accounts\Models\LedgerEntry;
use Src\Domain\Accounts\Models\Transaction;
use Src\Domain\Accounts\States\Account\Blocked;
use Src\Interfaces\Events\Account\FundsWereDeposited;

beforeEach(function () {
    Event::fake();

    $this->account = AccountFactory::new()->create();

    AccountBalance::register($this->account)->save();

    $this->headers = ['X-Correlation-ID' => Str::uuid()->toString()];
});

describe('POST /api/v1/account/{accountId}/deposit', function () {
    it('credits the balance and returns 204', function () {
        $this->postJson("/api/v1/account/{$this->account->id}/deposit", [
            'amount' => 5000,
            'description' => 'salary',
        ], $this->headers)->assertNoContent();

        expect(AccountBalance::where('account_id', $this->account->id)->value('available_balance'))->toBe(5000);
    });

    it('persists a Completed transaction', function () {
        $this->postJson("/api/v1/account/{$this->account->id}/deposit", [
            'amount' => 750,
            'description' => 'gift',
        ], $this->headers)->assertNoContent();

        $this->assertDatabaseHas('transactions', [
            'origin_account_id' => $this->account->id,
            'amount' => 750,
            'status' => 'completed',
        ], 'accounts');
    });

    it('persists a credit ledger entry', function () {
        $this->postJson("/api/v1/account/{$this->account->id}/deposit", [
            'amount' => 1234,
            'description' => 'payment',
        ], $this->headers)->assertNoContent();

        $this->assertDatabaseHas('ledger_entries', [
            'account_id' => $this->account->id,
            'amount' => 1234,
            'balance_after' => 1234,
            'type' => 'credit',
            'category' => 'deposit',
        ], 'accounts');
    });

    it('dispatches FundsWereDeposited event', function () {
        Event::fake([FundsWereDeposited::class]);

        $this->postJson("/api/v1/account/{$this->account->id}/deposit", [
            'amount' => 100,
            'description' => 'd',
        ], $this->headers)->assertNoContent();

        Event::assertDispatched(FundsWereDeposited::class);
    });

    it('returns 422 when amount is missing', function () {
        $this->postJson("/api/v1/account/{$this->account->id}/deposit", [
            'description' => 'd',
        ], $this->headers)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    });

    it('returns 422 when amount is below 1', function () {
        $this->postJson("/api/v1/account/{$this->account->id}/deposit", [
            'amount' => 0,
            'description' => 'd',
        ], $this->headers)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    });

    it('persists no transaction when validation fails', function () {
        $this->postJson("/api/v1/account/{$this->account->id}/deposit", [
            'amount' => 0,
            'description' => 'd',
        ], $this->headers)->assertUnprocessable();

        expect(Transaction::where('origin_account_id', $this->account->id)->count())->toBe(0);
        expect(LedgerEntry::where('account_id', $this->account->id)->count())->toBe(0);
    });

    it('throws AccountNotFoundException when the account does not exist', function () {
        $this->withoutExceptionHandling();

        expect(fn () => $this->postJson('/api/v1/account/00000000-0000-0000-0000-000000000000/deposit', [
            'amount' => 100,
            'description' => 'd',
        ], $this->headers))->toThrow(AccountNotFoundException::class);
    });

    it('throws AccountNotActiveException when the account is blocked', function () {
        $this->account->status->transitionTo(Blocked::class);
        $this->account->save();

        $this->withoutExceptionHandling();

        expect(fn () => $this->postJson("/api/v1/account/{$this->account->id}/deposit", [
            'amount' => 100,
            'description' => 'd',
        ], $this->headers))->toThrow(AccountNotActiveException::class);
    });
});
