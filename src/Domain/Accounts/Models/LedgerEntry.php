<?php

namespace Src\Domain\Accounts\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Src\Domain\Accounts\Enums\LedgerEntryTypeEnum;
use Src\Domain\Accounts\Enums\LedgerEntryCategory;

/**
 * @property string $id
 * @property string $account_id
 * @property LedgerEntryTypeEnum $type
 * @property int $amount
 * @property int $balance_after
 * @property string $description
 * @property LedgerEntryCategory $category
 * @property string $transaction_id
 * @property string $correlation_id
 * @property string|null $metadata
 * @property Carbon $occurred_at
 *
 * @property-read Account $account
*/
class LedgerEntry extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $connection = 'accounts';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'type' => LedgerEntryTypeEnum::class,
            'category' => LedgerEntryCategory::class,
            'occurred_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
