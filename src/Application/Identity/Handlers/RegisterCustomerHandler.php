<?php

namespace Src\Application\Identity\Handlers;

use Illuminate\Support\Facades\DB;
use Src\Application\Identity\DataObjects\CustomerData;
use Src\Application\Identity\DataObjects\RegisterCustomerData;
use Src\Domain\Identity\Exceptions\CpfAlreadyExistsException;
use Src\Domain\Identity\Exceptions\EmailAlreadyExistsException;
use Src\Domain\Identity\Models\Customer;
use Src\Domain\Identity\Models\CustomerAddress;
use Src\Domain\Identity\ValueObjects\Cpf;
use Throwable;

readonly class RegisterCustomerHandler
{
    /**
     * @throws Throwable
     */
    public function __invoke(RegisterCustomerData $customerData): CustomerData
    {
        $cpfDigits = new Cpf($customerData->cpf)->digits();

        if (Customer::where('cpf', $cpfDigits)->exists()) {
            throw new CpfAlreadyExistsException($customerData->cpf);
        }

        if (Customer::where('email', $customerData->email)->exists()) {
            throw new EmailAlreadyExistsException($customerData->email);
        }

        $customer = DB::connection('identity')
            ->transaction(function () use ($customerData) {
                $customer = Customer::register($customerData);
                $customerAddress = CustomerAddress::register($customerData->address, $customer);

                $customer->save();
                $customerAddress->save();

                return $customer;
            });

        return CustomerData::fromModel($customer->refresh());
    }
}
