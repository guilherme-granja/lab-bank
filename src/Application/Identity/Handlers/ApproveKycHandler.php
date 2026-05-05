<?php

namespace Src\Application\Identity\Handlers;

use Illuminate\Support\Facades\DB;
use Src\Application\Identity\DataObjects\ApproveKycData;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Exceptions\KycVerificationNotFound;
use Src\Domain\Identity\Models\Customer;
use Src\Domain\Identity\Models\KycVerification;
use Throwable;

class ApproveKycHandler
{
    /**
     * @throws Throwable
     */
    public function __invoke(ApproveKycData $approveKycData): void
    {
        $customer = Customer::find($approveKycData->customerId);

        throw_if(
            condition: is_null($customer),
            exception: CustomerNotFoundException::class,
        );

        /** @var KycVerification|null $kycVerification */
        $kycVerification = KycVerification::activeForCustomer($customer->id)->first();

        throw_if(
            condition: is_null($kycVerification),
            exception: KycVerificationNotFound::class,
        );

        DB::connection('identity')->transaction(function () use ($customer, $kycVerification) {
            $customer->approveKyc();
            $customer->activateAccount();
            $kycVerification->approve();
        });
    }
}
