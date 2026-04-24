<?php

namespace Src\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Spatie\ModelStates\HasStates;
use Src\Domain\Identity\Enums\Kyc\DocumentTypeEnum;
use Src\Domain\Identity\States\KycVerification as KycVerificationState;

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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public static function register(
        array $paths,
        string $customerId,
        DocumentTypeEnum $documentType,
        string $documentNumber,
    ): self {
        $kycVerification = new self;
        $kycVerification->customer_id = $customerId;
        $kycVerification->document_type = $documentType;
        $kycVerification->document_number = $documentNumber;
        $kycVerification->document_front_url = $paths['document_front_url'];
        $kycVerification->document_back_url = $paths['document_back_url'];
        $kycVerification->selfie_url = $paths['document_selfie_url'];

        return $kycVerification;
    }

    /**
     * @throws CouldNotPerformTransition
     */
    public function approve(): void
    {
        $this->status->transitionTo(KycVerificationState\Approved::class);
        $this->reviewed_at = now();
    }

    /**
     * @throws CouldNotPerformTransition
     */
    public function reject(string $reason): void
    {
        $this->status->transitionTo(KycVerificationState\Rejected::class);
        $this->reviewed_at = now();
        $this->rejection_reason = $reason;
    }

    /**
     * @throws CouldNotPerformTransition
     */
    public function startReview(): void
    {
        $this->status->transitionTo(KycVerificationState\Processing::class);
    }
}
