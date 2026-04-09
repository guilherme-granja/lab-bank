<?php

namespace Src\Domain\Identity\Contracts;

use Src\Domain\Identity\Models\CustomerAddress;

interface CustomerAddressRepositoryContract
{
    public function save(CustomerAddress $customerAddress): void;
}
