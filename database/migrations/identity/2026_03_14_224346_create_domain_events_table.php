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
        Schema::create('domain_events', static function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('aggregate_type');
            $table->uuid('aggregate_id');
            $table->integer('aggregate_version');
            $table->string('event_type');
            $table->json('payload');
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');

            $table->unique(['aggregate_id', 'aggregate_version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_events');
    }
};
