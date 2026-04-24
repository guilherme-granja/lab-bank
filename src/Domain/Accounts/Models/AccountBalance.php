<?php

namespace Src\Domain\Accounts\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $account_id
 * @property int $available_balance
 * @property int $blocked_amount
 * @property Carbon $last_updated_at
 * @property Account $account
 */
class AccountBalance extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $connection = 'accounts';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'account_id' => 'string',
            'available_balance' => 'int',
            'blocked_amount' => 'int',
            'last_updated_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public static function register(Account $account): self
    {
        $balance = new self;
        $balance->account_id = $account->id;
        $balance->available_balance = 0;
        $balance->blocked_amount = 0;
        $balance->last_updated_at = now();

        return $balance;
    }
}
