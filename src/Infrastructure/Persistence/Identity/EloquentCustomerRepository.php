<?php

namespace Src\Infrastructure\Persistence\Identity;

use Src\Domain\Identity\Contracts\CustomerRepositoryContract;
use Src\Domain\Identity\Models\Customer;

class EloquentCustomerRepository implements CustomerRepositoryContract
{
    public function save(Customer $customer): void
    {
        $customer->save();
    }

    public function findById(string $id): ?Customer
    {
        return Customer::query()
            ->find($id);
    }

    public function findByCpf(string $cpf): ?Customer
    {
        return Customer::query()
            ->where('cpf', $cpf)
            ->first();
    }

    public function findByEmail(string $email): ?Customer
    {
        return Customer::query()
            ->where('email', $email)
            ->first();
    }

    public function existsByCpf(string $cpf): bool
    {
        return Customer::query()
            ->where('cpf', $cpf)
            ->exists();
    }

    public function existsByEmail(string $email): bool
    {
        return Customer::query()
            ->where('email', $email)
            ->exists();
    }
}
