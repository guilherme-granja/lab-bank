<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Domain\Identity\Models\Customer;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    private static array $validCpfs = [
        '52998224725',
        '11144477735',
        '00000000191',
        '01234567890',
        '98765432100',
    ];

    private static int $cpfIndex = 0;

    public function definition(): array
    {
        $cpf = self::$validCpfs[self::$cpfIndex % count(self::$validCpfs)];
        self::$cpfIndex++;

        return [
            'full_name' => $this->faker->name(),
            'cpf' => $cpf,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '(11) 98765-4321',
            'birth_date' => '1990-01-15',
            'mother_name' => $this->faker->name('female'),
            'nationality' => 'BRA',
            'kyc_status' => 'pending',
            'status' => 'pending_kyc',
        ];
    }

    public function withKycApproved(): static
    {
        return $this->state([
            'kyc_status' => 'approved',
            'status' => 'active',
        ]);
    }

    public function withKycProcessing(): static
    {
        return $this->state([
            'kyc_status' => 'processing',
        ]);
    }

    public function withKycRejected(): static
    {
        return $this->state([
            'kyc_status' => 'rejected',
        ]);
    }

    public function withStatusBlocked(string $reason = 'Suspicious activity'): static
    {
        return $this->state([
            'kyc_status' => 'approved',
            'status' => 'blocked',
            'blocked_reason' => $reason,
        ]);
    }
}
