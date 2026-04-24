<?php

use Database\Factories\CustomerFactory;
use Illuminate\Support\Facades\Event;
use Src\Infrastructure\Persistence\Identity\EloquentCustomerRepository;

beforeEach(function () {
    Event::fake();
    $this->repository = new EloquentCustomerRepository;
});

describe('EloquentCustomerRepository', function () {
    it('saves and retrieves a customer by id', function () {
        $customer = CustomerFactory::new()->create();

        $found = $this->repository->findById($customer->id);

        expect($found)->not->toBeNull();
        expect($found->id)->toBe($customer->id);
    });

    it('returns null when findById finds no customer', function () {
        expect($this->repository->findById('non-existent-uuid'))->toBeNull();
    });

    it('finds a customer by cpf', function () {
        $customer = CustomerFactory::new()->create(['cpf' => '52998224725']);

        $found = $this->repository->findByCpf('52998224725');

        expect($found)->not->toBeNull();
        expect($found->id)->toBe($customer->id);
    });

    it('returns null when findByCpf finds no customer', function () {
        expect($this->repository->findByCpf('00000000000'))->toBeNull();
    });

    it('finds a customer by email', function () {
        $customer = CustomerFactory::new()->create(['email' => 'test@example.com']);

        $found = $this->repository->findByEmail('test@example.com');

        expect($found)->not->toBeNull();
        expect($found->id)->toBe($customer->id);
    });

    it('returns null when findByEmail finds no customer', function () {
        expect($this->repository->findByEmail('nobody@example.com'))->toBeNull();
    });

    it('existsByCpf returns true when cpf exists', function () {
        CustomerFactory::new()->create(['cpf' => '52998224725']);

        expect($this->repository->existsByCpf('52998224725'))->toBeTrue();
    });

    it('existsByCpf returns false when cpf does not exist', function () {
        expect($this->repository->existsByCpf('00000000000'))->toBeFalse();
    });

    it('existsByEmail returns true when email exists', function () {
        CustomerFactory::new()->create(['email' => 'test@example.com']);

        expect($this->repository->existsByEmail('test@example.com'))->toBeTrue();
    });

    it('existsByEmail returns false when email does not exist', function () {
        expect($this->repository->existsByEmail('nobody@example.com'))->toBeFalse();
    });
});
