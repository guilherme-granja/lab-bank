<?php

namespace Src\Interfaces\Http\Controllers\Identity;

use Illuminate\Http\JsonResponse;
use Src\Application\Identity\DataObjects\ApproveKycData;
use Src\Application\Identity\Handlers\ApproveKycHandler;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApproveKycController
{
    public function __construct(
        protected ApproveKycHandler $approveKycHandler
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(ApproveKycData $request): JsonResponse
    {
        ($this->approveKycHandler)($request);

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
