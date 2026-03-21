<?php

use Illuminate\Support\Facades\Route;
use Src\Interfaces\Http\Controllers\Identity\RegisterCustomerController;
use Src\Interfaces\Http\Controllers\Identity\SubmitKycDocumentsController;

Route::prefix('identity')->name('identity.')->group(function () {
    Route::prefix('customer')->name('customer.')->group(function () {
        Route::post('', RegisterCustomerController::class)
            ->name('register');

        Route::prefix('{customerId}')->group(function () {
            Route::post('kyc/documents', SubmitKycDocumentsController::class)
                ->name('kyc.documents.submit');
        });
    });
});
