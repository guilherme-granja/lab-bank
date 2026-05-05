<?php

namespace Src\Application\Identity\Handlers;

use Illuminate\Support\Facades\DB;
use Src\Application\Identity\DataObjects\CustomerData;
use Src\Application\Identity\DataObjects\RegisterCustomerData;
use Src\Domain\Identity\Exceptions\CpfAlreadyExistsException;
use Src\Domain\Identity\Exceptions\EmailAlreadyExistsException;
use Src\Domain\Identity\Models\Customer;
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

        $customer = DB::connection('identity')->transaction(function () use ($customerData) {
            $customer = Customer::create([
                'full_name' => $customerData->fullName,
                'cpf' => $customerData->cpf,
                'email' => $customerData->email,
                'phone' => $customerData->phone,
                'birth_date' => $customerData->birthDate,
                'mother_name' => $customerData->motherName,
                'nationality' => $customerData->nationality,
            ]);

            $customer->registerEvent();

            $customer->customerAddresses()->create([
                'zip_code' => $customerData->address->zipCode,
                'street' => $customerData->address->street,
                'number' => $customerData->address->number,
                'complement' => $customerData->address->complement,
                'neighborhood' => $customerData->address->neighborhood,
                'city' => $customerData->address->city,
                'state' => $customerData->address->state,
                'country' => $customerData->address->country,
                'is_primary' => $customerData->address->isPrimary,
            ]);

            return $customer;
        });

        return CustomerData::fromModel($customer->refresh());
    }
}
