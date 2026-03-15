<?php

namespace Src\Application\Identity\DataObjects;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class AddressData extends Data
{
    public function __construct(
        public string $zipCode,
        public string $street,
        public string $number,
        public ?string $complement,
        public string $neighborhood,
        public string $city,
        #[Max(2)]
        public string $state,
        #[Max(3)]
        public string $country,
        public bool $isPrimary = true,
    ) {}
}
