<?php

namespace Src\Application\Identity\DataObjects;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class RegisterCustomerData extends Data
{
    public function __construct(
        public string $fullName,
        #[Rule('cpf')]
        public string $cpf,
        #[Rule('email')]
        public string $email,
        #[Rule('celular_com_ddd')]
        public string $phone,
        #[Date]
        public string $birthDate,
        public string $motherName,
        #[Max(3)]
        public string $nationality,
        public AddressData $address,
    ) {}
}
