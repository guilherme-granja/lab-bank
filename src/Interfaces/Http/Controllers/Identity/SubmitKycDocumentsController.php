<?php

namespace Src\Interfaces\Http\Controllers\Identity;

use Illuminate\Http\JsonResponse;
use Src\Application\Identity\DataObjects\SubmitKycDocumentsData;
use Src\Application\Identity\Handlers\SubmitKycDocumentsHandler;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SubmitKycDocumentsController
{
    public function __construct(
        protected SubmitKycDocumentsHandler $submitKycDocuments,
    ) {}

    /**
     * @throws Throwable
     */
    public function __invoke(SubmitKycDocumentsData $request): JsonResponse
    {
        ($this->submitKycDocuments)($request);

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
