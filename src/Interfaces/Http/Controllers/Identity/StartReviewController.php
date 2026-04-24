<?php

namespace Src\Interfaces\Http\Controllers\Identity;

use Illuminate\Http\JsonResponse;
use Src\Application\Identity\DataObjects\StartKycReviewData;
use Src\Application\Identity\Handlers\StartKycReviewHandler;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class StartReviewController
{
    public function __construct(
        protected StartKycReviewHandler $startKycReviewHandler
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(StartKycReviewData $request): JsonResponse
    {
        ($this->startKycReviewHandler)($request);

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
