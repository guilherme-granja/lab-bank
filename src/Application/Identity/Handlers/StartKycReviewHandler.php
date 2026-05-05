<?php

namespace Src\Application\Identity\Handlers;

use Illuminate\Support\Facades\DB;
use Src\Application\Identity\DataObjects\StartKycReviewData;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Exceptions\KycVerificationNotFound;
use Src\Domain\Identity\Models\Customer;
use Src\Domain\Identity\Models\KycVerification;
use Throwable;

class StartKycReviewHandler
{
    /**
     * @throws Throwable
     */
    public function __invoke(StartKycReviewData $startKycReviewData): void
    {
        $customer = Customer::find($startKycReviewData->customerId);

        throw_if(
            condition: is_null($customer),
            exception: CustomerNotFoundException::class,
        );

        /** @var KycVerification $kycVerification */
        $kycVerification = KycVerification::activeForCustomer($customer->id)->first();

        throw_if(
            condition: is_null($kycVerification),
            exception: KycVerificationNotFound::class,
        );

        DB::connection('identity')->transaction(function () use ($customer, $kycVerification) {
            $customer->startKycReview();
            $kycVerification->startReview();
        });
    }
}
