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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->string('unit')->default('kg');

            // Dinh dưỡng trên mỗi đơn vị (ví dụ 100g hoặc 1kg)
            $table->decimal('calories', 10, 2)->default(0);
            $table->decimal('protein', 10, 2)->default(0);
            $table->decimal('lipid', 10, 2)->default(0);
            $table->decimal('glucid', 10, 2)->default(0);
            $table->decimal('fiber', 10, 2)->default(0)->nullable();

            $table->decimal('current_price', 15, 2)->default(0);
            $table->json('tags')->nullable(); // Ví dụ: 'hải sản', 'đồ chay'

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
