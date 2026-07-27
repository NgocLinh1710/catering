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
        Schema::table('daily_menus', function (Blueprint $table) {

            // Chi phí thực tế của 1 suất tại thời điểm lưu thực đơn
            $table->decimal('actual_cost_per_serving', 12, 2)
                ->default(0)
                ->after('allergy_notes');

            // Tổng chi phí thực tế của cả thực đơn
            $table->decimal('actual_total_cost', 14, 2)
                ->default(0)
                ->after('actual_cost_per_serving');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_menus', function (Blueprint $table) {

            $table->dropColumn([
                'actual_cost_per_serving',
                'actual_total_cost'
            ]);

        });
    }
};