<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'accounts';

    public function up(): void
    {
        Schema::create('sequences', static function (Blueprint $table) {
            $table->string('name')->primary();
            $table->unsignedBigInteger('last_value')->default(0);
            $table->timestamps();
        });

        // Initial seed sequences
        DB::table('sequences')->insert([
            'name' => 'account_number',
            'last_value' => 1000000000,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('sequences');
    }
};
