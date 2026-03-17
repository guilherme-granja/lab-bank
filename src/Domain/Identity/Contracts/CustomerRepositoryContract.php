<?php

namespace Src\Domain\Identity\Contracts;

use Src\Domain\Identity\Models\Customer;

interface CustomerRepositoryContract
{
    public function save(Customer $customer): void;

    public function findById(string $id): ?Customer;

    public function findByCpf(string $cpf): ?Customer;

    public function findByEmail(string $email): ?Customer;

    public function existsByCpf(string $cpf): bool;

    public function existsByEmail(string $email): bool;
}
