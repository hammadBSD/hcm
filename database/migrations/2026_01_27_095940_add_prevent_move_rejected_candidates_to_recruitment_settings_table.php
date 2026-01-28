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
        Schema::table('recruitment_settings', function (Blueprint $table) {
            $table->boolean('prevent_move_rejected_candidates')->default(false)->after('require_rating_before_move');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_settings', function (Blueprint $table) {
            $table->dropColumn('prevent_move_rejected_candidates');
        });
    }
};
