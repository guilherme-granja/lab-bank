<?php

use Illuminate\Support\Facades\Route;
use Src\Interfaces\Http\Controllers\Identity\RegisterCustomerController;

Route::prefix('identity')->group(function () {
    Route::post('customer', RegisterCustomerController::class)->name('customer.register');
})->name('identity');
