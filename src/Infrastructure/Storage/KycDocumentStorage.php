<?php

namespace Src\Infrastructure\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Src\Application\Identity\DataObjects\SubmitKycDocumentsData;
use Src\Domain\Identity\Exceptions\DocumentNotUploaded;
use Throwable;

class KycDocumentStorage
{
    /**
     * @throws Throwable
     */
    public function uploadKycDocuments(SubmitKycDocumentsData $submitKycDocumentsData, string $customerId): array
    {
        $kycDocuments['document_front_url'] = $this->upload(
            file: $submitKycDocumentsData->documentFront,
            customerId: $customerId,
            type: 'document-front'
        );

        $kycDocuments['document_back_url'] = ! is_null($submitKycDocumentsData->documentBack) ?
            $this->upload(
                file: $submitKycDocumentsData->documentBack,
                customerId: $customerId,
                type: 'document-back'
            ) : null;

        $kycDocuments['document_selfie_url'] = $this->upload(
            file: $submitKycDocumentsData->selfie,
            customerId: $customerId,
            type: 'document-selfie'
        );

        throw_if(
            ! $kycDocuments['document_front_url'],
            DocumentNotUploaded::class,
            'document_front'
        );

        throw_if(
            ! is_null($submitKycDocumentsData->documentBack) && ! $kycDocuments['document_back_url'],
            DocumentNotUploaded::class,
            'document_back'
        );

        throw_if(
            ! $kycDocuments['document_selfie_url'],
            DocumentNotUploaded::class,
            'document_selfie'
        );

        return $kycDocuments;
    }

    private function upload(UploadedFile $file, string $customerId, string $type): false|string
    {
        $directory = sprintf(
            'kyc/%s',
            $customerId
        );

        $name = sprintf(
            '%s-%s.%s',
            $type,
            Str::uuid()->toString(),
            $file->getClientOriginalExtension()
        );

        return Storage::putFileAs(
            path: $directory,
            file: $file,
            name: $name,
        );
    }
}
