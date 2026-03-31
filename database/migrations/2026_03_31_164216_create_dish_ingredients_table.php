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
        Schema::create('dish_ingredients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dish_id')->constrained('dishes')->onDelete('cascade');

            $table->foreignId('ingredient_id')->constrained('ingredients')->onDelete('cascade');

            // Định lượng của nguyên liệu đó trong món ăn (VD: 0.5 kg)
            $table->decimal('quantity', 8, 3);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dish_ingredients');
    }
};
