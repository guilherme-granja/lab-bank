<?php

namespace Src\Application\Identity\DataObjects;

use Spatie\LaravelData\Attributes\FromRouteParameter;
use Spatie\LaravelData\Data;

class RejectKycData extends Data
{
    public function __construct(
        #[FromRouteParameter('customerId')]
        public string $customerId,
        public string $reason,
    ) {}
}
