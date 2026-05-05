<?php

namespace Src\Application\Identity\DataObjects;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class KycDocumentStorageData extends Data
{
    public function __construct(
        public string $documentFrontUrl,
        public ?string $documentBackUrl,
        public string $documentSelfieUrl,
    ) {}
}
