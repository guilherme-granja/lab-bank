<?php

namespace Src\Application\Identity\DataObjects;

use Spatie\LaravelData\Attributes\FromRouteParameter;
use Spatie\LaravelData\Data;

class ApproveKycData extends Data
{
    public function __construct(
        #[FromRouteParameter('customerId')]
        public string $customerId,
    ) {}
}
