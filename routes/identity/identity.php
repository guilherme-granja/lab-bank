<?php

use Illuminate\Support\Facades\Route;
use Src\Interfaces\Http\Controllers\Identity\ApproveKycController;
use Src\Interfaces\Http\Controllers\Identity\RegisterCustomerController;
use Src\Interfaces\Http\Controllers\Identity\RejectKycController;
use Src\Interfaces\Http\Controllers\Identity\StartReviewController;
use Src\Interfaces\Http\Controllers\Identity\SubmitKycDocumentsController;

Route::prefix('identity')->name('identity.')->group(function () {
    Route::prefix('customer')->name('customer.')->group(function () {
        Route::post('', RegisterCustomerController::class)
            ->name('register');

        Route::prefix('{customerId}')->group(function () {
            Route::prefix('kyc')->name('kyc.')->group(function () {
                Route::post('documents', SubmitKycDocumentsController::class)
                    ->name('documents.submit');

                Route::post('approve', ApproveKycController::class)
                    ->name('approve');

                Route::post('reject', RejectKycController::class)
                    ->name('reject');

                Route::post('start-review', StartReviewController::class)
                    ->name('start-review');
            });
        });
    });
});
