<?php

use Database\Factories\CustomerFactory;
use Illuminate\Support\Facades\Event;
use Src\Domain\Identity\Exceptions\CpfAlreadyExistsException;
use Src\Domain\Identity\Exceptions\EmailAlreadyExistsException;
use Src\Interfaces\Events\Identity\CustomerWasRegistered;

beforeEach(function () {
    Event::fake();
});

describe('POST /api/v1/identity/customer', function () {
    it('registers a new customer and returns 201 with customer data', function () {
        $this->postJson('/api/v1/identity/customer', validCustomerPayload())
            ->assertCreated()
            ->assertJsonStructure([
                'id',
                'full_name',
                'cpf',
                'email',
                'phone',
                'birth_date',
                'mother_name',
                'nationality',
                'kyc_status',
                'status',
                'created_at',
                'updated_at',
            ])
            ->assertJsonFragment([
                'full_name' => 'João da Silva',
                'cpf' => '52998224725',
                'email' => 'joao.silva@example.com',
                'kyc_status' => 'pending',
                'status' => 'pending_kyc',
            ]);
    });

    it('accepts cpf with formatting and stores only digits', function () {
        $this->postJson('/api/v1/identity/customer', validCustomerPayload([
            'cpf' => '529.982.247-25',
        ]))
            ->assertCreated()
            ->assertJsonFragment(['cpf' => '52998224725']);
    });

    it('dispatches CustomerWasRegistered event on success', function () {
        Event::fake([CustomerWasRegistered::class]);

        $this->postJson('/api/v1/identity/customer', validCustomerPayload())
            ->assertCreated();

        Event::assertDispatched(CustomerWasRegistered::class);
    });

    it('returns 422 when full_name is missing', function () {
        $payload = validCustomerPayload();
        unset($payload['full_name']);

        $this->postJson('/api/v1/identity/customer', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['full_name']);
    });

    it('returns 422 when cpf is invalid', function () {
        $this->postJson('/api/v1/identity/customer', validCustomerPayload([
            'cpf' => '111.111.111-11',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cpf']);
    });

    it('returns 422 when email is invalid', function () {
        $this->postJson('/api/v1/identity/customer', validCustomerPayload([
            'email' => 'not-an-email',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    it('returns 422 when phone format is invalid', function () {
        $this->postJson('/api/v1/identity/customer', validCustomerPayload([
            'phone' => '11987654321',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    });

    it('returns 422 when birth_date is not a valid date', function () {
        $this->postJson('/api/v1/identity/customer', validCustomerPayload([
            'birth_date' => 'not-a-date',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['birth_date']);
    });

    it('returns 422 when nationality exceeds 3 characters', function () {
        $this->postJson('/api/v1/identity/customer', validCustomerPayload([
            'nationality' => 'BRAZ',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nationality']);
    });

    it('throws CpfAlreadyExistsException when cpf already exists', function () {
        CustomerFactory::new()->create(['cpf' => '52998224725']);

        $this->withoutExceptionHandling();

        expect(fn () => $this->postJson('/api/v1/identity/customer', validCustomerPayload()))
            ->toThrow(CpfAlreadyExistsException::class);
    });

    it('throws EmailAlreadyExistsException when email already exists', function () {
        CustomerFactory::new()->create([
            'cpf' => '11144477735',
            'email' => 'joao.silva@example.com',
        ]);

        $this->withoutExceptionHandling();

        expect(fn () => $this->postJson('/api/v1/identity/customer', validCustomerPayload()))
            ->toThrow(EmailAlreadyExistsException::class);
    });
});
