<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();

            $table->string('name');
            $table->string('category')->nullable();

            $table->decimal('price', 12, 2)->default(0);

            $table->decimal('calories', 10, 2)->default(0);
            $table->decimal('protein', 8, 2)->default(0)->nullable();
            $table->decimal('lipid', 8, 2)->default(0)->nullable();
            $table->decimal('glucid', 8, 2)->default(0)->nullable();

            $table->text('instructions')->nullable();
            $table->json('dish_tags')->nullable();
            $table->string('image_url')->nullable();

            $table->timestamps();

            // Nếu xóa công ty thì xóa luôn món ăn của công ty đó
            $table->foreign('company_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};