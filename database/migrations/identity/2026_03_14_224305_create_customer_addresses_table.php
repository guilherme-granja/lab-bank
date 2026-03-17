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
        Schema::create('customer_addresses', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('zip_code', 9);
            $table->string('street');
            $table->string('number', 20);
            $table->string('complement')->nullable();
            $table->string('neighborhood');
            $table->string('city');
            $table->string('state', 2);
            $table->string('country', 3)->default('BRA');
            $table->boolean('is_primary')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
