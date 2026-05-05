<?php

namespace Src\Application\Identity\Handlers;

use Illuminate\Support\Facades\DB;
use Src\Application\Identity\DataObjects\RejectKycData;
use Src\Domain\Identity\Exceptions\CustomerNotFoundException;
use Src\Domain\Identity\Exceptions\KycVerificationNotFound;
use Src\Domain\Identity\Models\Customer;
use Src\Domain\Identity\Models\KycVerification;
use Throwable;

class RejectKycHandler
{
    /**
     * @throws Throwable
     */
    public function __invoke(RejectKycData $rejectKycData): void
    {
        $customer = Customer::find($rejectKycData->customerId);

        throw_if(
            condition: is_null($customer),
            exception: CustomerNotFoundException::class,
        );

        $kycVerification = KycVerification::where('customer_id', $customer->id)->first();

        throw_if(
            condition: is_null($kycVerification),
            exception: KycVerificationNotFound::class,
        );

        DB::connection('identity')->transaction(function () use ($customer, $kycVerification, $rejectKycData) {
            $customer->rejectKyc($rejectKycData->reason);
            $kycVerification->reject($rejectKycData->reason);
        });
    }
}
