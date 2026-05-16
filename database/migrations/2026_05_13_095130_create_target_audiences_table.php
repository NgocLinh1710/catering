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
        Schema::create('target_audiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Ví dụ: Khối 3 tuổi, Tổ công nhân,...

            // Thiết lập dị ứng & tôn giáo 
            $table->json('allergy_tags')->nullable(); // ["Không ăn đậu phộng", "Dị ứng hải sản"]
            $table->json('religion_tags')->nullable(); // ["Không ăn thịt lợn"]

            // Thiết lập tiêu chuẩn dinh dưỡng (Định mức cần đạt)
            $table->decimal('target_calories', 8, 2)->default(0);
            $table->decimal('target_protein', 8, 2)->default(0);
            $table->decimal('target_fat', 8, 2)->default(0);
            $table->decimal('target_fiber', 8, 2)->default(0);

            // Thiết lập ngân sách
            $table->decimal('budget_per_serving', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_audiences');
    }
};
