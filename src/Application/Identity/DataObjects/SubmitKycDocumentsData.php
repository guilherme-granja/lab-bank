<?php

namespace Src\Application\Identity\DataObjects;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Attributes\FromRouteParameter;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\File;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Mimes;
use Spatie\LaravelData\Attributes\Validation\MimeTypes;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Src\Domain\Identity\Enums\Kyc\DocumentTypeEnum;

#[MapName(SnakeCaseMapper::class)]
class SubmitKycDocumentsData extends Data
{
    public function __construct(
        #[FromRouteParameter('customerId')]
        public string           $customerId,
        public DocumentTypeEnum $documentType,
        public string           $documentNumber,
        #[File, Mimes('jpg', 'jpeg', 'png', 'pdf'), MimeTypes('image/jpeg', 'image/png', 'application/pdf'), Max(5120)]
        public UploadedFile     $documentFront,
        #[File, Mimes('jpg', 'jpeg', 'png', 'pdf'), MimeTypes('image/jpeg', 'image/png', 'application/pdf'), Max(5120)]
        public ?UploadedFile    $documentBack,
        #[File, Mimes('jpg', 'jpeg', 'png'), MimeTypes('image/jpeg', 'image/png'), Max(2048)]
        public UploadedFile     $selfie,
    ) {}
}
