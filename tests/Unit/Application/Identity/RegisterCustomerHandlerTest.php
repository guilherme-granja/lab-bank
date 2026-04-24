<?php

use Illuminate\Support\Facades\Event;
use Src\Application\Identity\DataObjects\AddressData;
use Src\Application\Identity\DataObjects\CustomerData;
use Src\Application\Identity\DataObjects\RegisterCustomerData;
use Src\Application\Identity\Handlers\RegisterCustomerHandler;
use Src\Domain\Identity\Contracts\CustomerAddressRepositoryContract;
use Src\Domain\Identity\Contracts\CustomerRepositoryContract;
use Src\Domain\Identity\Exceptions\CpfAlreadyExistsException;
use Src\Domain\Identity\Exceptions\EmailAlreadyExistsException;
use Src\Domain\Identity\Models\Customer;
use Src\Domain\Identity\Models\CustomerAddress;

use function Pest\Laravel\mock;

beforeEach(function () {
    Event::fake();

    $this->customerRepository = mock(CustomerRepositoryContract::class);
    $this->customerAddressRepository = mock(CustomerAddressRepositoryContract::class);

    $this->handler = new RegisterCustomerHandler(
        $this->customerRepository,
        $this->customerAddressRepository,
    );

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
    it('saves the customer and address via their repositories', function () {
        $this->customerRepository->shouldReceive('existsByCpf')->once()->andReturn(false);
        $this->customerRepository->shouldReceive('existsByEmail')->once()->andReturn(false);
        $this->customerRepository->shouldReceive('save')->once()
            ->with(Mockery::type(Customer::class))
            ->andReturnUsing(fn (Customer $customer) => $customer->save());
        $this->customerAddressRepository->shouldReceive('save')->once()
            ->with(Mockery::type(CustomerAddress::class));

        ($this->handler)($this->data);
    });

    it('returns a CustomerData DTO with the correct fields', function () {
        $this->customerRepository->shouldReceive('existsByCpf')->once()->andReturn(false);
        $this->customerRepository->shouldReceive('existsByEmail')->once()->andReturn(false);
        $this->customerRepository->shouldReceive('save')->once()
            ->andReturnUsing(fn (Customer $customer) => $customer->save());
        $this->customerAddressRepository->shouldReceive('save')->once();

        $result = ($this->handler)($this->data);

        expect($result)->toBeInstanceOf(CustomerData::class);
        expect($result->fullName)->toBe('João da Silva');
        expect($result->cpf)->toBe('52998224725');
        expect($result->email)->toBe('joao@example.com');
    });

    it('throws CpfAlreadyExistsException when the cpf already exists', function () {
        $this->customerRepository->shouldReceive('existsByCpf')->once()->andReturn(true);

        expect(fn () => ($this->handler)($this->data))
            ->toThrow(CpfAlreadyExistsException::class);
    });

    it('skips the email check when the cpf already exists', function () {
        $this->customerRepository->shouldReceive('existsByCpf')->once()->andReturn(true);
        $this->customerRepository->shouldNotReceive('existsByEmail');

        expect(fn () => ($this->handler)($this->data))
            ->toThrow(CpfAlreadyExistsException::class);
    });

    it('throws EmailAlreadyExistsException when the email already exists', function () {
        $this->customerRepository->shouldReceive('existsByCpf')->once()->andReturn(false);
        $this->customerRepository->shouldReceive('existsByEmail')->once()->andReturn(true);

        expect(fn () => ($this->handler)($this->data))
            ->toThrow(EmailAlreadyExistsException::class);
    });

    it('does not save when the cpf already exists', function () {
        $this->customerRepository->shouldReceive('existsByCpf')->once()->andReturn(true);
        $this->customerRepository->shouldNotReceive('save');
        $this->customerAddressRepository->shouldNotReceive('save');

        expect(fn () => ($this->handler)($this->data))->toThrow(CpfAlreadyExistsException::class);
    });

    it('does not save when the email already exists', function () {
        $this->customerRepository->shouldReceive('existsByCpf')->once()->andReturn(false);
        $this->customerRepository->shouldReceive('existsByEmail')->once()->andReturn(true);
        $this->customerRepository->shouldNotReceive('save');
        $this->customerAddressRepository->shouldNotReceive('save');

        expect(fn () => ($this->handler)($this->data))->toThrow(EmailAlreadyExistsException::class);
    });
});
