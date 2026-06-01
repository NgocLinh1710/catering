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
        Schema::create('target_audience_restrictions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('target_audience_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('name');
            $table->string('tag')->nullable();
            $table->text('note')->nullable();
            $table->integer('default_quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_audience_restrictions');
    }
};
