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
        Schema::create('ingredient_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 15, 2); // Giá tại thời điểm này
            $table->date('applied_date');    // Ngày bắt đầu áp dụng giá này
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_prices');
    }
};
