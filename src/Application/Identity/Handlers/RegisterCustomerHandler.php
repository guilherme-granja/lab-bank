<?php

namespace Src\Application\Identity\Handlers;

use Illuminate\Support\Facades\DB;
use Src\Application\Identity\DataObjects\CustomerData;
use Src\Application\Identity\DataObjects\RegisterCustomerData;
use Src\Domain\Identity\Contracts\CustomerAddressRepositoryContract;
use Src\Domain\Identity\Contracts\CustomerRepositoryContract;
use Src\Domain\Identity\Exceptions\CpfAlreadyExistsException;
use Src\Domain\Identity\Exceptions\EmailAlreadyExistsException;
use Src\Domain\Identity\Models\Customer;
use Src\Domain\Identity\Models\CustomerAddress;
use Src\Domain\Identity\ValueObjects\Cpf;
use Throwable;

readonly class RegisterCustomerHandler
{
    public function __construct(
        protected CustomerRepositoryContract $customerRepository,
        protected CustomerAddressRepositoryContract $customerAddressRepository,
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(RegisterCustomerData $customerData): CustomerData
    {
        $cpfDigits = new Cpf($customerData->cpf)->digits();

        if ($this->customerRepository->existsByCpf($cpfDigits)) {
            throw new CpfAlreadyExistsException($customerData->cpf);
        }

        if ($this->customerRepository->existsByEmail($customerData->email)) {
            throw new EmailAlreadyExistsException($customerData->email);
        }

        $customer = DB::connection('identity')
            ->transaction(function () use ($customerData) {
                $customer = Customer::register($customerData);
                $customerAddress = CustomerAddress::register($customerData->address, $customer);

                $this->customerRepository->save($customer);
                $this->customerAddressRepository->save($customerAddress);

                return $customer;
            });

        return CustomerData::fromModel($customer->refresh());
    }
}
