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
        Schema::create('employee_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('type', ['suggestion', 'complaint'])->default('suggestion');
            $table->string('complaint_type')->nullable(); // Only for complaints: system_issues, attendance, leaves, employee, other
            $table->text('message');
            $table->enum('status', ['pending', 'in_progress', 'resolved', 'dismissed'])->default('pending');
            $table->text('admin_response')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_suggestions');
    }
};
