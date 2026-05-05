<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Domain\Accounts\Enums\AccountTypeEnum;
use Src\Domain\Accounts\Models\Account;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'customer_id' => $this->faker->uuid(),
            'account_number' => '1000000123',
            'account_type' => AccountTypeEnum::Checking,
            'activated_at' => now(),
        ];
    }
}
