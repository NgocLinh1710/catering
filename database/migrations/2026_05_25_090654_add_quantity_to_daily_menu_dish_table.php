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
        Schema::table('daily_menu_dish', function (Blueprint $table) {

            $table->integer('quantity')
                ->default(1)
                ->after('dish_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_menu_dish', function (Blueprint $table) {

            $table->dropColumn('quantity');

        });
    }
};
