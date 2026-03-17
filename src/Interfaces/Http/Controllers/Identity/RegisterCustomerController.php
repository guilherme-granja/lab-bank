<?php

namespace Src\Interfaces\Http\Controllers\Identity;

use Illuminate\Http\JsonResponse;
use Src\Application\Identity\DataObjects\RegisterCustomerData;
use Src\Application\Identity\Handlers\RegisterCustomer;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

readonly class RegisterCustomerController
{
    public function __construct(
        private RegisterCustomer $registerCustomer
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(RegisterCustomerData $customerData): JsonResponse
    {
        $response = ($this->registerCustomer)($customerData);

        return response()->json($response, Response::HTTP_CREATED);
    }
}
