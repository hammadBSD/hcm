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
        Schema::create('device_attendances', function (Blueprint $table) {
            $table->id();
            $table->string('punch_code');
            $table->string('device_ip');
            $table->enum('device_type', ['IN', 'OUT']);
            $table->dateTime('punch_time');
            $table->enum('punch_type', ['check_in', 'check_out', 'break_out', 'break_in'])->nullable();
            $table->enum('status', ['On Time', 'Late', 'Absent'])->nullable();
            $table->integer('verify_mode')->nullable();
            $table->boolean('is_processed')->default(false);
            $table->timestamp('sync_timestamp')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['punch_code', 'punch_time'], 'device_attendances_punch_code_punch_time_index');
            $table->index(['device_ip', 'punch_time'], 'device_attendances_device_ip_punch_time_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_attendances');
    }
};
