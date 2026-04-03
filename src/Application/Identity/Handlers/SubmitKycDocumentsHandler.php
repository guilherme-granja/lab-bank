<?php

namespace Src\Application\Identity\Handlers;

use Illuminate\Support\Facades\DB;
use Src\Application\Identity\DataObjects\SubmitKycDocumentsData;
use Src\Domain\Identity\Contracts\CustomerRepositoryContract;
use Src\Domain\Identity\Contracts\KycVerificationRepositoryContract;
use Src\Domain\Identity\Exceptions\CustomerCantSubmitKyc;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Models\KycVerification;
use Src\Infrastructure\Storage\KycDocumentStorage;
use Throwable;

readonly class SubmitKycDocumentsHandler
{
    public function __construct(
        protected CustomerRepositoryContract $customerRepository,
        protected KycVerificationRepositoryContract $kycVerificationRepository,
        protected KycDocumentStorage $kycDocumentStorage,
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(SubmitKycDocumentsData $submitKycDocumentsData): void
    {
        $customer = $this->customerRepository->findById($submitKycDocumentsData->customerId);

        if (is_null($customer)) {
            throw new CustomerNotFoundException();
        }

        if (! $customer->canSubmmitKyc()) {
            throw new CustomerCantSubmitKyc($customer->kyc_status);
        }

        $paths = $this->kycDocumentStorage->uploadKycDocuments($submitKycDocumentsData, $customer->id);

        $kycVerification = KycVerification::register(
            paths: $paths,
            customerId: $customer->id,
            documentType: $submitKycDocumentsData->documentType,
            documentNumber: $submitKycDocumentsData->documentNumber,
        );

        DB::connection('identity')->transaction(function () use ($kycVerification) {
            $this->kycVerificationRepository->save($kycVerification);
        });
    }
}
