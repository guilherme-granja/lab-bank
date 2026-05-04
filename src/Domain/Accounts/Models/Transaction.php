<?php

namespace Src\Domain\Accounts\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Spatie\ModelStates\HasStates;
use Src\Domain\Accounts\Enums\TransactionTypeEnum;
use Src\Domain\Accounts\States\Transaction\Completed;
use Src\Domain\Accounts\States\Transaction\Processing;
use Src\Domain\Accounts\States\TransactionStatus;

/**
 * @property string $id
 * @property string $correlation_id
 * @property string|null $origin_account_id
 * @property string|null $destination_account_id
 * @property int $amount
 * @property string $currency
 * @property TransactionTypeEnum $type
 * @property TransactionStatus $status
 * @property string|null $description
 * @property string|null $failure_reason
 * @property string|null $metadata
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Transaction extends Model
{
    use HasStates;
    use HasUuids;

    protected $connection = 'accounts';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'amount' => 'int',
            'type' => TransactionTypeEnum::class,
            'status' => TransactionStatus::class,
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public static function register(
        string $correlationId,
        int $amount,
        TransactionTypeEnum $type,
        ?string $originAccountId = null,
        ?string $destinationAccountId = null,
        ?string $description = null,
    ): self {
        $transaction = new self;
        $transaction->id = $transaction->newUniqueId();
        $transaction->correlation_id = $correlationId;
        $transaction->amount = $amount;
        $transaction->type = $type;
        $transaction->origin_account_id = $originAccountId;
        $transaction->destination_account_id = $destinationAccountId;
        $transaction->description = $description;

        return $transaction;
    }

    /**
     * @throws CouldNotPerformTransition
     */
    public function process(): void
    {
        $this->status->transitionTo(Processing::class);
    }

    /**
     * @throws CouldNotPerformTransition
     */
    public function complete(): void
    {
        $this->completed_at = now();
        $this->status->transitionTo(Completed::class);
    }
}
