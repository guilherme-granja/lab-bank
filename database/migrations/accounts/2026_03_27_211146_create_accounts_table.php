<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Src\Domain\Accounts\Enums\AccountTypeEnum;

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
        Schema::create('accounts', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            $table->string('account_number', 20)->unique();
            $table->string('branch', 4)->default('0001');
            $table->string('bank_code', 3)->default('000');
            $table->enum('account_type', AccountTypeEnum::values());
            $table->string('status');
            $table->string('blocked_reason')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('customer_id');
            $table->index('account_number');
            $table->index('status');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
