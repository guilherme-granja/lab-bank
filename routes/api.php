<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    require base_path('routes/identity/identity.php');
});
