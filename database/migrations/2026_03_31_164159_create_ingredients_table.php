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
            // company_id phân biệt dữ liệu của công ty nào 
            $table->unsignedBigInteger('company_id')->index();

            $table->string('name');
            $table->string('unit', 50); // kg, l, gram...

            // Dinh dưỡng chuẩn (Tính trên 100g/100ml)
            $table->decimal('calories', 8, 2)->default(0);
            $table->decimal('protein', 8, 2)->default(0);
            $table->decimal('fat', 8, 2)->default(0);
            $table->decimal('carb', 8, 2)->default(0);
            $table->decimal('fiber', 8, 2)->default(0);

            $table->decimal('current_price', 12, 2)->default(0); // Giá tiền hiện tại

            // Dùng JSON để lưu mảng các Tag dị ứng/tôn giáo linh hoạt
            $table->json('tags')->nullable();

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
