<?php

namespace Src\Infrastructure\Persistence\Identity;

use Src\Domain\Identity\Contracts\KycVerificationRepositoryContract;
use Src\Domain\Identity\Models\KycVerification;

class EloquentKycVerificationRepository implements KycVerificationRepositoryContract
{
    public function save(KycVerification $kycVerification): void
    {
        $kycVerification->save();
    }

    public function findByCustomerId(string $customerId): ?KycVerification
    {
        return KycVerification::query()
            ->where('customer_id', $customerId)
            ->first();
    }
}
