<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->boolean('lock_after_shift')->default(false);
            $table->boolean('mandatory')->default(false);
            $table->boolean('split_periods')->default(false); // Enable first_half/second_half split
            $table->integer('lock_grace_period_minutes')->default(0); // Grace period after shift ends
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_settings');
    }
};
