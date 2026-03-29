<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Src\Domain\Accounts\Enums\TransactionType;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'accounts';

    public function up(): void
    {
        Schema::create('transactions', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('correlation_id')->unique();
            $table->uuid('origin_account_id')->nullable();
            $table->uuid('destination_account_id')->nullable();
            $table->bigInteger('amount')->comment('em centavos');
            $table->string('currency', 3)->default('BRL');
            $table->enum('type', TransactionType::values());
            $table->string('status');
            $table->string('description')->nullable();
            $table->string('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('correlation_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
