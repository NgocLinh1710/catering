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
        Schema::create('daily_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('target_audience_id')->constrained('target_audiences')->onDelete('cascade');
            $table->date('date');
            $table->integer('servings')->default(100);
            $table->timestamps();

            // Tránh việc trùng lặp thực đơn cho cùng một nhóm trong cùng một ngày
            $table->unique(['target_audience_id', 'date']);
        });

        // Bảng trung gian liên kết thực đơn với các món ăn
        Schema::create('daily_menu_dish', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_menu_id')->constrained('daily_menus')->onDelete('cascade');
            $table->foreignId('dish_id')->constrained('dishes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_menu_dish');
        Schema::dropIfExists('daily_menus');
    }
};
