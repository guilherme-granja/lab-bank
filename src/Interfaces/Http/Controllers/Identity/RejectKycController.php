<?php

namespace Src\Interfaces\Http\Controllers\Identity;

use Illuminate\Http\JsonResponse;
use Src\Application\Identity\DataObjects\RejectKycData;
use Src\Application\Identity\Handlers\RejectKycHandler;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RejectKycController
{
    public function __construct(
        protected RejectKycHandler $rejectKycHandler,
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(RejectKycData $request): JsonResponse
    {
        ($this->rejectKycHandler)($request);
        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
