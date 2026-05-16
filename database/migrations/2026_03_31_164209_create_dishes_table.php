<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('company_id')->constrained('users');
            $table->foreignId('created_by')->constrained('users');

            $table->string('category')->nullable(); // Món chính, món phụ...
            $table->decimal('price', 15, 2)->default(0);
            $table->text('instructions')->nullable(); // Hướng dẫn nấu ăn
            $table->json('dish_tags')->nullable(); // Lưu mảng ["cay", "nóng"]...

            // Chỉ số dinh dưỡng
            $table->decimal('total_calories', 10, 2)->default(0);
            $table->decimal('total_protein', 10, 2)->default(0);
            $table->decimal('lipid', 10, 2)->default(0);
            $table->decimal('glucid', 10, 2)->default(0);

            $table->decimal('estimated_cost', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};