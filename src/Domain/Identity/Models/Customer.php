<?php

namespace Src\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Spatie\ModelStates\HasStates;
use Src\Domain\Identity\Events\Customer\CustomerActivatedEvent;
use Src\Domain\Identity\Events\Customer\CustomerBlockedEvent;
use Src\Domain\Identity\Events\Customer\CustomerRegisteredEvent;
use Src\Domain\Identity\Events\Customer\KycApprovedEvent;
use Src\Domain\Identity\Events\Customer\KycRejectedEvent;
use Src\Domain\Identity\Observers\CustomerObserver;
use Src\Domain\Identity\States\Customer\Active;
use Src\Domain\Identity\States\Customer\Blocked;
use Src\Domain\Identity\States\Kyc\Approved;
use Src\Domain\Identity\States\Kyc\Pending;
use Src\Domain\Identity\States\Kyc\Processing;
use Src\Domain\Identity\States\Kyc\Rejected;
use Src\Domain\Identity\States\KycStatus;
use Src\Domain\Identity\States\Status;
use Src\Shared\Traits\AggregateRoot;

/**
 * @property string $id
 * @property string $full_name
 * @property string $cpf
 * @property string $email
 * @property string $phone
 * @property Carbon $birth_date
 * @property string $mother_name
 * @property string $nationality
 * @property KycStatus $kyc_status
 * @property Status $status
 * @property string $blocked_reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection $kycVerifications
 * @property-read Collection $customerAddresses
 */
#[ObservedBy(CustomerObserver::class)]
class Customer extends Model
{
    use AggregateRoot;
    use HasFactory;
    use HasStates;
    use HasUuids;
    use SoftDeletes;

    protected $connection = 'identity';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'birth_date' => 'date',
            'kyc_status' => KycStatus::class,
            'status' => Status::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    protected $guarded = ['id'];

    public function kycVerifications(): HasMany|self
    {
        return $this->hasMany(KycVerification::class);
    }

    public function customerAddresses(): HasMany|self
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function registerEvent(): void
    {
        $this->recordEvent(new CustomerRegisteredEvent($this));

        $this->fireModelEvent('created', false);
    }

    /**
     * @throws CouldNotPerformTransition
     */
    public function startKycReview(): void
    {
        $this->kyc_status->transitionTo(Processing::class);
    }

    /**
     * @throws CouldNotPerformTransition
     */
    public function approveKyc(): void
    {
        $this->recordEvent(new KycApprovedEvent($this));

        $this->kyc_status->transitionTo(Approved::class);
    }

    /**
     * @throws CouldNotPerformTransition
     */
    public function rejectKyc(string $reason): void
    {
        $this->recordEvent(new KycRejectedEvent($this, $reason));

        $this->kyc_status->transitionTo(Rejected::class);
    }

    /**
     * @throws CouldNotPerformTransition
     */
    public function block(string $reason): void
    {
        $this->recordEvent(new CustomerBlockedEvent($this, $reason));

        $this->status->transitionTo(Blocked::class);
    }

    public function canOperate(): bool
    {
        return $this->kyc_status instanceof Approved &&
            $this->status instanceof Active;
    }

    public function canSubmmitKyc(): bool
    {
        return $this->kyc_status instanceof Pending ||
            $this->kyc_status instanceof Rejected;
    }

    /**
     * @throws CouldNotPerformTransition
     */
    public function activateAccount(): void
    {
        $this->recordEvent(new CustomerActivatedEvent($this));

        $this->status->transitionTo(Active::class);
    }
}
