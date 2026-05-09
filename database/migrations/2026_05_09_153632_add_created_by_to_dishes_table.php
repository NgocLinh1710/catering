<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->double('total_protein')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            //
        });
    }
};
