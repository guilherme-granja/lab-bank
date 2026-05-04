<?php

namespace Src\Interfaces\Http\Controllers\Account;

use Illuminate\Http\JsonResponse;
use Src\Application\Accounts\DataObjects\DepositData;
use Src\Application\Accounts\Handlers\DepositHandler;
use Symfony\Component\HttpFoundation\Response;

class DepositController
{
    public function __construct(
        public DepositHandler $depositHandler,
    ) {}

    public function __invoke(DepositData $request): JsonResponse
    {
        ($this->depositHandler)($request);

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
