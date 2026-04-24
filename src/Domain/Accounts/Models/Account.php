<?php

namespace Src\Domain\Accounts\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\ModelStates\HasStates;
use Src\Domain\Accounts\Enums\AccountTypeEnum;
use Src\Domain\Accounts\Events\Account\AccountOpenedEvent;
use Src\Domain\Accounts\Observers\AccountObserver;
use Src\Domain\Accounts\States\AccountStatus;
use Src\Shared\Traits\AggregateRoot;

/**
 * @property string $id
 * @property string $customer_id
 * @property string $account_number
 * @property string $branch
 * @property string $bank_code
 * @property AccountTypeEnum $account_type
 * @property AccountStatus $status
 * @property string|null $blocked_reason
 * @property Carbon|null $activated_at
 * @property Carbon|null $closed_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read LedgerEntry[] $ledgerEntries
 * @property-read AccountBalance $balance
 */
#[ObservedBy(AccountObserver::class)]
class Account extends Model
{
    use AggregateRoot;
    use HasStates;
    use HasUuids;
    use SoftDeletes;

    protected $connection = 'accounts';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'account_type' => AccountTypeEnum::class,
            'status' => AccountStatus::class,
            'activated_at' => 'datetime',
            'closed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function balance(): HasOne
    {
        return $this->hasOne(AccountBalance::class);
    }

    public static function register(string $customerId, AccountTypeEnum $accountTypeEnum, string $accountNumber): self
    {
        $account = new self;
        $account->id = $account->newUniqueId();
        $account->customer_id = $customerId;
        $account->account_number = $accountNumber;
        $account->account_type = $accountTypeEnum;
        $account->activated_at = now();

        $account->recordEvent(new AccountOpenedEvent($account));

        return $account;
    }
}
