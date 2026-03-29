<?php

namespace Src\Application\Identity\Handlers;

use Illuminate\Support\Facades\DB;
use Src\Application\Identity\DataObjects\ApproveKycData;
use Src\Domain\Identity\Contracts\CustomerRepositoryContract;
use Src\Domain\Identity\Contracts\KycVerificationRepositoryContract;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Exceptions\KycVerificationNotFound;
use Throwable;

class ApproveKycHandler
{
    public function __construct(
        protected CustomerRepositoryContract $customerRepository,
        protected KycVerificationRepositoryContract $kycVerificationRepository,
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(ApproveKycData $approveKycData): void
    {
        $customer = $this->customerRepository->findById($approveKycData->customerId);

        throw_if(
            condition: is_null($customer),
            exception: CustomerNotFoundException::class,
        );

        $kycVerification = $this->kycVerificationRepository->findActiveByCustomerId($customer->id);

        throw_if(
            condition: is_null($kycVerification),
            exception: KycVerificationNotFound::class,
        );

        DB::connection('identity')->transaction(function () use ($customer, $kycVerification) {
            $customer->approveKyc();
            $customer->activateAccount();
            $kycVerification->approve();

            $this->customerRepository->save($customer);
            $this->kycVerificationRepository->save($kycVerification);
        });
    }
}
