<?php

namespace Src\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Spatie\ModelStates\HasStates;
use Src\Domain\Identity\Enums\Kyc\DocumentTypeEnum;
use Src\Domain\Identity\States\KycVerification as KycVerificationState;
use Src\Domain\Identity\States\KycVerification\Pending;
use Src\Domain\Identity\States\KycVerification\Processing;

/**
 * @property string $id
 * @property string $customer_id
 * @property KycVerificationState $status
 * @property DocumentTypeEnum $document_type
 * @property string $document_number
 * @property string $document_front_url
 * @property string|null $document_back_url
 * @property string $selfie_url
 * @property string|null $provider
 * @property string|null $provider_response
 * @property float|null $confidence_score
 * @property string|null $rejection_reason
 * @property Carbon|null $reviewed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Customer $customer
 */
class KycVerification extends Model
{
    use HasStates;
    use HasUuids;

    protected $connection = 'identity';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'status' => KycVerificationState::class,
            'document_type' => DocumentTypeEnum::class,
            'confidence_score' => 'float',
            'reviewed_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    protected $guarded = ['id', 'customer_id'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    protected function scopeActiveForCustomer(Builder $query, string $customerId): Builder
    {
        return $query->where('customer_id', $customerId)
            ->whereState('status', [Pending::class, Processing::class]);
    }

    /**
     * @throws CouldNotPerformTransition
     */
    public function approve(): void
    {
        $this->update(['reviewed_at' => now()]);

        $this->status->transitionTo(KycVerificationState\Approved::class);
    }

    /**
     * @throws CouldNotPerformTransition
     */
    public function reject(string $reason): void
    {
        $this->update([
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->status->transitionTo(KycVerificationState\Rejected::class);
    }

    /**
     * @throws CouldNotPerformTransition
     */
    public function startReview(): void
    {
        $this->status->transitionTo(Processing::class);
    }
}
