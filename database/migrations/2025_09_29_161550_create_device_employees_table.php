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
        Schema::create('device_employees', function (Blueprint $table) {
            $table->id();
            $table->string('punch_code_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('device_ip')->nullable();
            $table->string('device_type')->nullable();
            $table->timestamps();
            
            // Unique index
            $table->unique('punch_code_id', 'device_employees_punch_code_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_employees');
    }
};
