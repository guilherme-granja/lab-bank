<?php

namespace Src\Application\Identity\DataObjects;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Src\Domain\Identity\Models\Customer;

#[MapName(SnakeCaseMapper::class)]
class CustomerData extends Data
{
    public function __construct(
        public string $id,
        public string $fullName,
        public string $cpf,
        public string $email,
        public string $phone,
        public string $birthDate,
        public string $motherName,
        public string $nationality,
        public string $kycStatus,
        public string $status,
        public ?string $blockedReason,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt,
    ) {}

    public static function fromModel(Customer $customer): self
    {
        return new self(
            $customer->id,
            $customer->full_name,
            $customer->cpf,
            $customer->email,
            $customer->phone,
            $customer->birth_date->toDateString(),
            $customer->mother_name,
            $customer->nationality,
            $customer->kyc_status::getMorphClass(),
            $customer->status::getMorphClass(),
            $customer->blocked_reason,
            $customer->created_at->toDateTimeString(),
            $customer->updated_at->toDateTimeString(),
            $customer->deleted_at?->toDateTimeString(),
        );
    }
}
