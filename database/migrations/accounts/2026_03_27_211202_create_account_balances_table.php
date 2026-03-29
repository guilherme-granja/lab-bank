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
    protected $connection = 'accounts';

    public function up(): void
    {
        Schema::create('account_balances', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->bigInteger('available_balance')->comment('em centavos');
            $table->bigInteger('blocked_amount')->comment('em centavos');
            $table->timestamp('last_updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_balances');
    }
};
