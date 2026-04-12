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
        Schema::create('company_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('company_name'); // Tên công ty Catering
            $table->string('contact_person'); // Người đại diện
            $table->string('email')->unique(); // Email liên hệ
            $table->string('phone'); // Số điện thoại
            $table->string('status')->default('pending'); // Trạng thái: pending (chờ duyệt), approved (đã duyệt), rejected (từ chối)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_registrations');
    }
};
