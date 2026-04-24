<?php

namespace Tests;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase;

    /**
     * Tracks which bounded-context connections have had migrate:fresh run.
     * Keyed by connection name so each connection is handled independently.
     *
     * @var array<string, bool>
     */
    private static array $migratedConnections = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDomainConnections();
    }

    protected function tearDown(): void
    {
        $this->tearDownDomainConnections();
        parent::tearDown();
    }

    /**
     * Run migrate:fresh once per connection per suite, then open a transaction
     * that will be rolled back in tearDown — providing per-test isolation without
     * re-running migrations on every test.
     */
    private function setUpDomainConnections(): void
    {
        foreach ($this->domainConnections() as $connection => $migrationPath) {
            if (! array_key_exists($connection, static::$migratedConnections)) {
                $this->artisan('migrate:fresh', [
                    '--database' => $connection,
                    '--path' => $migrationPath,
                    '--force' => true,
                ]);

                static::$migratedConnections[$connection] = true;
            }

            DB::connection($connection)->beginTransaction();
        }
    }

    private function tearDownDomainConnections(): void
    {
        foreach (array_keys($this->domainConnections()) as $connection) {
            DB::connection($connection)->rollBack();
        }
    }

    /**
     * Bounded-context connections managed by this TestCase.
     * Override in a subclass to add or remove connections.
     *
     * @return array<string, string> connection => migration path
     */
    protected function domainConnections(): array
    {
        return [
            'identity' => 'database/migrations/identity',
            'accounts' => 'database/migrations/accounts',
        ];
    }
}
