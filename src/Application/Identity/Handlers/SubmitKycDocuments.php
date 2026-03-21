<?php

namespace Src\Application\Identity\Handlers;

use Illuminate\Support\Facades\DB;
use Src\Application\Identity\DataObjects\SubmitKycDocumentsData;
use Src\Domain\Identity\Contracts\CustomerRepositoryContract;
use Src\Domain\Identity\Contracts\KycVerificationRepositoryContract;
use Src\Domain\Identity\Exceptions\CustomerCantSubmitKyc;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Exceptions\DocumentNotUploaded;
use Src\Domain\Identity\Models\Customer;
use Src\Domain\Identity\Models\KycVerification;
use Src\Infrastructure\Storage\KycDocumentStorage;
use Throwable;

readonly class SubmitKycDocuments
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

        throw_if(
            condition: is_null($customer),
            exception: CustomerNotFoundException::class,
        );

        throw_if(
            condition: ! $customer->canSubmmitKyc(),
            exception: CustomerCantSubmitKyc::class,
        );

        $paths = $this->kycDocumentStorage->uploadKycDocuments($submitKycDocumentsData, $customer->id);

        $kycVerification = KycVerification::register(
            paths: $paths,
            customerId: $customer->id,
            documentType: $submitKycDocumentsData->documentType,
            documentNumber: $submitKycDocumentsData->documentNumber,
        );

        DB::connection('identity')->transaction(function () use ($customer, $kycVerification) {
            $customer->startKycReview();
            $this->customerRepository->save($customer);
            $this->kycVerificationRepository->save($kycVerification);
        });
    }
}
