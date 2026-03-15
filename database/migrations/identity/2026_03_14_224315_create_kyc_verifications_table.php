<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Src\Domain\Identity\Enums\Kyc\DocumentType;

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
        Schema::create('kyc_verifications', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('status');
            $table->enum('document_type', DocumentType::values());
            $table->string('document_number');
            $table->string('document_front_url');
            $table->string('document_back_url')->nullable();
            $table->string('selfie_url');
            $table->string('provider')->default('manual');
            $table->string('provider_response')->nullable();
            $table->decimal('confidence_score', 5)->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_verifications');
    }
};
