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
        Schema::table('device_attendances', function (Blueprint $table) {
            $table->boolean('is_manual_entry')->nullable()->after('is_processed');
            $table->foreignId('updated_by')->nullable()->after('is_manual_entry')->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable()->after('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_attendances', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['is_manual_entry', 'updated_by', 'notes']);
        });
    }
};
