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
        Schema::table('daily_menus', function (Blueprint $table) {
            $table->integer('normal_servings')->default(0)->after('servings');
            $table->integer('vegetarian_servings')->default(0)->after('normal_servings');
            $table->integer('allergy_servings')->default(0)->after('vegetarian_servings');
            $table->text('allergy_notes')->nullable()->after('allergy_servings');
        });

        Schema::table('daily_menu_dish', function (Blueprint $table) {
            // meal_type nhận 3 giá trị: 'normal' (thường), 'vegetarian' (chay), 'allergy' (dị ứng)
            $table->string('meal_type')->default('normal')->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_menu_dish', function (Blueprint $table) {
            $table->dropColumn('meal_type');
        });

        Schema::table('daily_menus', function (Blueprint $table) {
            $table->dropColumn(['normal_servings', 'vegetarian_servings', 'allergy_servings', 'allergy_notes']);
        });
    }
};