<?php

namespace Src\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Spatie\ModelStates\HasStates;
use Src\Application\Identity\DataObjects\RegisterCustomerData;
use Src\Domain\Identity\Events\Customer\CustomerBlocked;
use Src\Domain\Identity\Events\Customer\CustomerRegistered;
use Src\Domain\Identity\Events\Customer\KycApproved;
use Src\Domain\Identity\Events\Customer\KycRejected;
use Src\Domain\Identity\Observers\CustomerObserver;
use Src\Domain\Identity\States\Customer\Active;
use Src\Domain\Identity\States\Customer\Blocked;
use Src\Domain\Identity\States\Kyc\Approved;
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
 */
#[ObservedBy(CustomerObserver::class)]
class Customer extends Model
{
    use SoftDeletes;
    use HasStates;
    use AggregateRoot;
    use HasUuids;

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

    public static function register(RegisterCustomerData $customerData): self
    {
        $customer = new Customer();
        $customer->full_name = $customerData->fullName;
        $customer->cpf = new Cpf($customerData->cpf)->digits();
        $customer->email = $customerData->email;
        $customer->phone = $customerData->phone;
        $customer->birth_date = $customerData->birthDate;
        $customer->mother_name = $customerData->motherName;
        $customer->nationality = $customerData->nationality;

        $customer->recordEvent(new CustomerRegistered($customer));

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
        $this->recordEvent(new KycApproved($this));
    }

    /**
     * @throws CouldNotPerformTransition
     */
    public function rejectKyc(string $reason): void
    {
        $this->kyc_status->transitionTo(Rejected::class);
        $this->recordEvent(new KycRejected($this, $reason));
    }

    /**
     * @throws CouldNotPerformTransition
     */
    public function block(string $reason): void
    {
        $this->status->transitionTo(Blocked::class);
        $this->recordEvent(new CustomerBlocked($this, $reason));
    }

    public function canOperate(): bool
    {
        return $this->kyc_status instanceof Approved &&
            $this->status instanceof Active;
    }
}
