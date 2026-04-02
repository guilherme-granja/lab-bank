<?php

use Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature', 'Unit');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

function validCustomerPayload(array $overrides = []): array
{
    return array_merge([
        'full_name' => 'João da Silva',
        'cpf' => '529.982.247-25',
        'email' => 'joao.silva@example.com',
        'phone' => '(11) 98765-4321',
        'birth_date' => '1990-01-15',
        'mother_name' => 'Maria da Silva',
        'nationality' => 'BRA',
        'address' => [
            'zip_code' => '01310-100',
            'street' => 'Avenida Paulista',
            'number' => '1000',
            'complement' => null,
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
            'country' => 'BRA',
            'is_primary' => true,
        ],
    ], $overrides);
}
