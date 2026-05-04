<?php

use Database\Factories\CustomerFactory;
use Illuminate\Support\Facades\Event;
use Src\Application\Identity\DataObjects\AddressData;
use Src\Application\Identity\DataObjects\CustomerData;
use Src\Application\Identity\DataObjects\RegisterCustomerData;
use Src\Application\Identity\Handlers\RegisterCustomerHandler;
use Src\Domain\Identity\Exceptions\CpfAlreadyExistsException;
use Src\Domain\Identity\Exceptions\EmailAlreadyExistsException;
use Src\Domain\Identity\Models\Customer;
use Src\Domain\Identity\Models\CustomerAddress;

beforeEach(function () {
    Event::fake();

    $this->handler = new RegisterCustomerHandler;

    $this->data = new RegisterCustomerData(
        fullName: 'João da Silva',
        cpf: '52998224725',
        email: 'joao@example.com',
        phone: '(11) 98765-4321',
        birthDate: '1990-01-15',
        motherName: 'Maria da Silva',
        nationality: 'BRA',
        address: new AddressData('01310-100', 'Av. Paulista', '1000', null, 'Bela Vista', 'São Paulo', 'SP', 'BRA'),
    );
});

describe('RegisterCustomerHandler', function () {
    it('saves the customer and address', function () {
        ($this->handler)($this->data);

        expect(Customer::where('cpf', '52998224725')->exists())->toBeTrue();
        expect(CustomerAddress::where('zip_code', '01310-100')->exists())->toBeTrue();
    });

    it('returns a CustomerData DTO with the correct fields', function () {
        $result = ($this->handler)($this->data);

        expect($result)->toBeInstanceOf(CustomerData::class);
        expect($result->fullName)->toBe('João da Silva');
        expect($result->cpf)->toBe('52998224725');
        expect($result->email)->toBe('joao@example.com');
    });

    it('throws CpfAlreadyExistsException when the cpf already exists', function () {
        CustomerFactory::new()->create(['cpf' => '52998224725']);

        expect(fn () => ($this->handler)($this->data))
            ->toThrow(CpfAlreadyExistsException::class);
    });

    it('throws EmailAlreadyExistsException when the email already exists', function () {
        CustomerFactory::new()->create(['cpf' => '11144477735', 'email' => 'joao@example.com']);

        expect(fn () => ($this->handler)($this->data))
            ->toThrow(EmailAlreadyExistsException::class);
    });

    it('does not save when the cpf already exists', function () {
        CustomerFactory::new()->create(['cpf' => '52998224725']);

        $countBefore = Customer::count();

        expect(fn () => ($this->handler)($this->data))->toThrow(CpfAlreadyExistsException::class);
        expect(Customer::count())->toBe($countBefore);
    });

    it('does not save when the email already exists', function () {
        CustomerFactory::new()->create(['cpf' => '11144477735', 'email' => 'joao@example.com']);

        $countBefore = Customer::count();

        expect(fn () => ($this->handler)($this->data))->toThrow(EmailAlreadyExistsException::class);
        expect(Customer::count())->toBe($countBefore);
    });
});
