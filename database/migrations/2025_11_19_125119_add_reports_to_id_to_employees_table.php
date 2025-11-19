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
        Schema::table('employees', function (Blueprint $table) {
            // Add foreign key for reports_to (self-referencing to employees table)
            $table->foreignId('reports_to_id')->nullable()->after('reports_to')->constrained('employees')->onDelete('set null');
            
            // Add index for performance
            $table->index('reports_to_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['reports_to_id']);
            $table->dropIndex(['reports_to_id']);
            $table->dropColumn('reports_to_id');
        });
    }
};
