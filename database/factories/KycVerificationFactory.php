<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Domain\Identity\Enums\Kyc\DocumentTypeEnum;
use Src\Domain\Identity\Models\KycVerification;

class KycVerificationFactory extends Factory
{
    protected $model = KycVerification::class;

    public function definition(): array
    {
        return [
            'customer_id' => null,
            'document_type' => DocumentTypeEnum::Cnh->value,
            'document_number' => $this->faker->numerify('###########'),
            'document_front_url' => 'kyc/customer-id/document-front-uuid.jpg',
            'document_back_url' => 'kyc/customer-id/document-back-uuid.jpg',
            'selfie_url' => 'kyc/customer-id/document-selfie-uuid.jpg',
            'provider' => 'manual',
            'status' => 'pending',
        ];
    }

    public function forCustomer(string $customerId): static
    {
        return $this->state(['customer_id' => $customerId]);
    }

    public function processing(): static
    {
        return $this->state(['status' => 'processing']);
    }

    public function approved(): static
    {
        return $this->state([
            'status' => 'approved',
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(string $reason = 'Documents unclear'): static
    {
        return $this->state([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_at' => now(),
        ]);
    }
}
