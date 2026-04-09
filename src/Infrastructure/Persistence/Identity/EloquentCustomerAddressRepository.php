<?php

namespace Src\Infrastructure\Persistence\Identity;

use Src\Domain\Identity\Contracts\CustomerAddressRepositoryContract;
use Src\Domain\Identity\Models\CustomerAddress;

class EloquentCustomerAddressRepository implements CustomerAddressRepositoryContract
{
    public function save(CustomerAddress $customerAddress): void
    {
        $customerAddress->save();
    }
}
