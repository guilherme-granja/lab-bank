<?php

namespace Src\Domain\Identity\Contracts;

use Src\Domain\Identity\Models\KycVerification;

interface KycVerificationRepositoryContract
{
    public function save(KycVerification $kycVerification): void;

    public function findByCustomerId(string $customerId): ?KycVerification;
}
