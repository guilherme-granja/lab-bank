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
        Schema::create('customers', static function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('full_name');
            $table->string('cpf', 11)->unique();
            $table->string('email')->unique();
            $table->string('phone', 20);
            $table->date('birth_date');
            $table->string('nationality', 3)->default('BRA');
            $table->string('kyc_status');
            $table->string('status');
            $table->text('blocked_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('cpf');
            $table->index('email');
            $table->index('kyc_status');
            $table->index('status');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
