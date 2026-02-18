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
        Schema::create('zkteco_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('sync_type'); // 'attendance', 'employees', 'monthly_attendance'
            $table->timestamp('synced_at');
            $table->timestamps();

            $table->index('synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zkteco_sync_logs');
    }
};
