<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Src\Domain\Accounts\Enums\LedgerEntriesTypeEnum;
use Src\Domain\Accounts\Enums\LedgerEntryCategory;

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
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('type', LedgerEntriesTypeEnum::values());
            $table->bigInteger('amount')->comment('em centavos');
            $table->bigInteger('balance_after')->comment('em centavos');
            $table->string('description');
            $table->enum('category', LedgerEntryCategory::values());
            $table->uuid('transaction_id');
            $table->uuid('correlation_id');
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');

            $table->index('account_id');
            $table->index('transaction_id');
            $table->index('correlation_id');
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
