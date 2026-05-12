<?php

use Database\Factories\AccountFactory;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Src\Application\Accounts\DataObjects\DepositData;
use Src\Application\Accounts\Handlers\DepositHandler;
use Src\Domain\Accounts\Exceptions\AccountNotActiveException;
use Src\Domain\Accounts\Exceptions\AccountNotFoundException;
use Src\Domain\Accounts\Models\Account;
use Src\Domain\Accounts\Models\AccountBalance;
use Src\Domain\Accounts\Models\LedgerEntry;
use Src\Domain\Accounts\Models\Transaction;
use Src\Domain\Accounts\States\Account\Blocked;
use Src\Domain\Accounts\States\Transaction\Completed;

beforeEach(function () {
    Event::fake();

    request()->headers->set('X-Correlation-ID', (string) Str::uuid());

    $this->handler = new DepositHandler;
});

function makeActiveAccountWithBalance(): Account
{
    $account = AccountFactory::new()->create();
    $account->save();
    $account->pullDomainEvents();

    $account->balance()->create([
        'available_balance' => 0,
        'blocked_amount' => 0,
        'last_updated_at' => now(),
    ]);

    return $account->refresh();
}

describe('DepositHandler', function () {
    it('credits the account balance by the deposited amount', function () {
        $account = makeActiveAccountWithBalance();

        ($this->handler)->handle(new DepositData(accountId: $account->id, amount: 5000, description: 'salary'));

        $balance = AccountBalance::where('account_id', $account->id)->first();
        expect($balance->available_balance)->toBe(5000);
    });

    it('persists a Transaction in Completed state', function () {
        $account = makeActiveAccountWithBalance();

        ($this->handler)->handle(new DepositData(accountId: $account->id, amount: 1000, description: 'deposit'));

        $transaction = Transaction::where('origin_account_id', $account->id)->first();
        expect($transaction)->not->toBeNull()
            ->and($transaction->status)->toBeInstanceOf(Completed::class)
            ->and($transaction->amount)->toBe(1000);
    });

    it('persists a credit LedgerEntry with balance_after', function () {
        $account = makeActiveAccountWithBalance();

        ($this->handler)->handle(new DepositData(accountId: $account->id, amount: 2500, description: 'top-up'));

        $entry = LedgerEntry::where('account_id', $account->id)->first();
        expect($entry)->not->toBeNull()
            ->and($entry->amount)->toBe(2500)
            ->and($entry->balance_after)->toBe(2500);
    });

    it('updates the account timestamp after deposit', function () {
        $account = makeActiveAccountWithBalance();
        $before = $account->updated_at;

        sleep(1);
        ($this->handler)->handle(new DepositData(accountId: $account->id, amount: 100, description: 'd'));

        $account->refresh();
        expect($account->updated_at->gt($before))->toBeTrue();
    });

    it('throws AccountNotFoundException when the account does not exist', function () {
        expect(fn () => ($this->handler)->handle(new DepositData(accountId: '00000000-0000-0000-0000-000000000000', amount: 100, description: 'd')))
            ->toThrow(AccountNotFoundException::class);
    });

    it('throws AccountNotActiveException when the account is not active', function () {
        $account = makeActiveAccountWithBalance();
        $account->status->transitionTo(Blocked::class);
        $account->save();

        expect(fn () => ($this->handler)->handle(new DepositData(accountId: $account->id, amount: 100, description: 'd')))
            ->toThrow(AccountNotActiveException::class);
    });

    it('does not credit balance when account is blocked', function () {
        $account = makeActiveAccountWithBalance();
        $account->status->transitionTo(Blocked::class);
        $account->save();

        try {
            ($this->handler)->handle(new DepositData(accountId: $account->id, amount: 999, description: 'd'));
        } catch (AccountNotActiveException) {
            // expected
        }

        expect(AccountBalance::where('account_id', $account->id)->value('available_balance'))->toBe(0)
            ->and(Transaction::where('origin_account_id', $account->id)->count())->toBe(0);
    });
});
