<?php

use Illuminate\Support\Facades\Route;
use Src\Interfaces\Http\Controllers\Account\DepositController;

Route::prefix('account')->name('account.')->group(function () {
    Route::prefix('{accountId}')->group(function () {
        Route::post('deposit', DepositController::class)
            ->name('deposit');
    });
});
