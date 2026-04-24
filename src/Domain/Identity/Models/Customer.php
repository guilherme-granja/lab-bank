<?php

namespace Src\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Spatie\ModelStates\HasStates;
use Src\Application\Identity\DataObjects\RegisterCustomerData;
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
use Src\Domain\Identity\ValueObjects\Cpf;
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
 * @property Carbon $deleted_at
 * @property-read Collection $kycVerifications
 * @property-read Collection $customerAddresses
 */
#[ObservedBy(CustomerObserver::class)]
class Customer extends Model
{
    use AggregateRoot;
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

    public function kycVerifications(): HasMany|self
    {
        return $this->hasMany(KycVerification::class);
    }

    public function customerAddresses(): HasMany|self
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public static function register(RegisterCustomerData $customerData): self
    {
        $customer = new self;
        $customer->id = $customer->newUniqueId();
        $customer->full_name = $customerData->fullName;
        $customer->cpf = new Cpf($customerData->cpf)->digits();
        $customer->email = $customerData->email;
        $customer->phone = $customerData->phone;
        $customer->birth_date = $customerData->birthDate;
        $customer->mother_name = $customerData->motherName;
        $customer->nationality = $customerData->nationality;

        $customer->recordEvent(new CustomerRegisteredEvent($customer));

        return $customer;
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
        $this->kyc_status->transitionTo(Approved::class);
        $this->recordEvent(new KycApprovedEvent($this));
    }

    /**
     * @throws CouldNotPerformTransition
     */
    public function rejectKyc(string $reason): void
    {
        $this->kyc_status->transitionTo(Rejected::class);
        $this->recordEvent(new KycRejectedEvent($this, $reason));
    }

    /**
     * @throws CouldNotPerformTransition
     */
    public function block(string $reason): void
    {
        $this->status->transitionTo(Blocked::class);
        $this->recordEvent(new CustomerBlockedEvent($this, $reason));
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
        $this->status->transitionTo(Active::class);
        $this->recordEvent(new CustomerActivatedEvent($this));
    }
}
