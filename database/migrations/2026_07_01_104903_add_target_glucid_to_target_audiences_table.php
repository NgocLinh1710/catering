<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('target_audiences', function (Blueprint $table) {
            $table->decimal('target_glucid', 8, 2)
                ->default(0)
                ->after('target_fat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('target_audiences', function (Blueprint $table) {
            $table->dropColumn('target_glucid');
        });
    }
};