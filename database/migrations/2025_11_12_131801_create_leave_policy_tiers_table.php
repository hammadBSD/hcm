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
        Schema::create('leave_policy_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_policy_id')->constrained('leave_policies')->cascadeOnDelete();
            $table->unsignedInteger('year_of_service');
            $table->decimal('additional_quota', 8, 2);
            $table->timestamps();

            $table->unique(['leave_policy_id', 'year_of_service']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_policy_tiers');
    }
};
