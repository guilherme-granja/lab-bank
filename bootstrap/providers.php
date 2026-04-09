<?php

use App\Providers\AppServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\TelescopeServiceProvider;
use Src\Infrastructure\Providers\AccountServiceProvider;
use Src\Infrastructure\Providers\IdentityServiceProvider;

return [
    AppServiceProvider::class,
    HorizonServiceProvider::class,
    TelescopeServiceProvider::class,
    IdentityServiceProvider::class,
    AccountServiceProvider::class,
];
