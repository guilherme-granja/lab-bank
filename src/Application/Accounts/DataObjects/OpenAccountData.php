<?php

namespace Src\Application\Accounts\DataObjects;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class OpenAccountData extends Data
{
    public function __construct(
        public string $customerId,
    ) {}
}
