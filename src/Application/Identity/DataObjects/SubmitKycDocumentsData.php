<?php

namespace Src\Application\Identity\DataObjects;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Src\Domain\Identity\Enums\Kyc\DocumentType;

#[MapName(SnakeCaseMapper::class)]
class SubmitKycDocumentsData extends Data
{
    public function __construct(
        public string $customerId,
        public DocumentType $documentType,
        public UploadedFile $documentFront,
        public ?UploadedFile $documentBack,
        public UploadedFile $selfie,
    ) {}
}
