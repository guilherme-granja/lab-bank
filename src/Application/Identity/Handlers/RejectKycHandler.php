<?php

namespace Src\Application\Identity\Handlers;

use Illuminate\Support\Facades\DB;
use Src\Application\Identity\DataObjects\RejectKycData;
use Src\Domain\Identity\Contracts\CustomerRepositoryContract;
use Src\Domain\Identity\Contracts\KycVerificationRepositoryContract;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Exceptions\KycVerificationNotFound;
use Throwable;

class RejectKycHandler
{
    public function __construct(
        protected CustomerRepositoryContract $customerRepository,
        protected KycVerificationRepositoryContract $kycVerificationRepository,
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(RejectKycData $rejectKycData): void
    {
        $customer = $this->customerRepository->findById($rejectKycData->customerId);

        throw_if(
            condition: is_null($customer),
            exception: CustomerNotFoundException::class,
        );

        $kycVerification = $this->kycVerificationRepository->findByCustomerId($customer->id);

        throw_if(
            condition: is_null($kycVerification),
            exception: KycVerificationNotFound::class,
        );

        DB::connection('identity')->transaction(function () use ($customer, $kycVerification, $rejectKycData) {
            $customer->rejectKyc($rejectKycData->reason);
            $kycVerification->reject($rejectKycData->reason);

            $this->customerRepository->save($customer);
            $this->kycVerificationRepository->save($kycVerification);
        });
    }
}
