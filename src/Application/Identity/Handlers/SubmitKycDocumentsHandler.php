<?php

namespace Src\Application\Identity\Handlers;

use Illuminate\Support\Facades\DB;
use Src\Application\Identity\DataObjects\SubmitKycDocumentsData;
use Src\Domain\Identity\Exceptions\CustomerCantSubmitKyc;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Models\Customer;
use Src\Domain\Identity\Models\KycVerification;
use Src\Infrastructure\Storage\KycDocumentStorage;
use Throwable;

readonly class SubmitKycDocumentsHandler
{
    public function __construct(
        protected KycDocumentStorage $kycDocumentStorage,
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(SubmitKycDocumentsData $submitKycDocumentsData): void
    {
        $customer = Customer::find($submitKycDocumentsData->customerId);

        if (is_null($customer)) {
            throw new CustomerNotFoundException;
        }

        if (! $customer->canSubmmitKyc()) {
            throw new CustomerCantSubmitKyc($customer->kyc_status);
        }

        $paths = $this->kycDocumentStorage->uploadKycDocuments($submitKycDocumentsData, $customer->id);

        DB::connection('identity')->transaction(function () use ($paths, $submitKycDocumentsData) {
            KycVerification::create([
                'document_type' => $submitKycDocumentsData->documentType,
                'document_number' => $submitKycDocumentsData->documentNumber,
                'document_front_url' => $paths->documentFrontUrl,
                'document_back_url' => $paths->documentBackUrl,
                'selfie_url' => $paths->documentSelfieUrl,
            ]);
        });
    }
}
