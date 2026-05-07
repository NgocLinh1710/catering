<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Bảng chính lưu tên món ăn
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('company_id')->constrained('users'); // Thuộc về công ty nào
            $table->foreignId('created_by')->constrained('users'); // Nhân viên nào tạo
            $table->decimal('total_calories', 10, 2)->default(0);
            $table->decimal('total_protein', 10, 2)->default(0);
            $table->decimal('estimated_cost', 10, 2)->default(0);
            $table->timestamps();
        });

        // Bảng phụ lưu chi tiết nguyên liệu trong món (Quan hệ n-n)
        Schema::create('dish_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained();
            $table->decimal('weight', 8, 3); // Khối lượng (kg), ví dụ 0.150kg
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};