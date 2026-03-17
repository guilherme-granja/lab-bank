<?php

namespace Src\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\ModelStates\HasStates;
use Src\Domain\Identity\Enums\Kyc\DocumentType;
use Src\Domain\Identity\States\KycVerification as KycVerificationState;

/**
 * @property string $id
 * @property string $customer_id
 * @property KycVerificationState $status
 * @property DocumentType $document_type
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
 */
class KycVerification extends Model
{
    use HasUuids;
    use HasStates;

    protected $connection = 'identity';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'status' => KycVerificationState::class,
            'document_type' => DocumentType::class,
            'confidence_score' => 'float',
            'reviewed_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
