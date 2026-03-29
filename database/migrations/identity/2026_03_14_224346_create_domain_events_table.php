<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'identity';

    public function up(): void
    {
        Blueprint::domainEvents();
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_events');
    }
};
