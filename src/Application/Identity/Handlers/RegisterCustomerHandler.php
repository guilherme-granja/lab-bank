<?php

namespace Src\Application\Identity\Handlers;

use Illuminate\Support\Facades\DB;
use Src\Application\Identity\DataObjects\CustomerData;
use Src\Application\Identity\DataObjects\RegisterCustomerData;
use Src\Domain\Identity\Contracts\CustomerRepositoryContract;
use Src\Domain\Identity\Exceptions\CpfAlreadyExistsException;
use Src\Domain\Identity\Exceptions\EmailAlreadyExistsException;
use Src\Domain\Identity\Models\Customer;
use Throwable;

readonly class RegisterCustomerHandler
{
    public function __construct(
        protected CustomerRepositoryContract $customerRepository,
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(RegisterCustomerData $customerData): CustomerData
    {
        if ($this->customerRepository->existsByCpf($customerData->cpf)) {
            throw new CpfAlreadyExistsException($customerData->cpf);
        }

        if ($this->customerRepository->existsByEmail($customerData->email)) {
            throw new EmailAlreadyExistsException($customerData->email);
        }

        $customer = DB::connection('identity')
            ->transaction(function () use ($customerData) {
                $customer = Customer::register($customerData);
                $this->customerRepository->save($customer);

                return $customer;
            });

        return CustomerData::fromModel($customer->refresh());
    }
}
