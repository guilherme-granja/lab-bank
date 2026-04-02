<?php

namespace Tests;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase;

    private static bool $identityDatabaseMigrated = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpIdentityDatabase();
    }

    protected function tearDown(): void
    {
        $this->tearDownIdentityDatabase();
        parent::tearDown();
    }

    private function setUpIdentityDatabase(): void
    {
        if (! static::$identityDatabaseMigrated) {
            $this->artisan('migrate:fresh', [
                '--database' => 'identity',
                '--path' => 'database/migrations/identity',
                '--force' => true,
            ]);

            static::$identityDatabaseMigrated = true;
        }

        DB::connection('identity')->beginTransaction();
    }

    private function tearDownIdentityDatabase(): void
    {
        DB::connection('identity')->rollBack();
    }
}
