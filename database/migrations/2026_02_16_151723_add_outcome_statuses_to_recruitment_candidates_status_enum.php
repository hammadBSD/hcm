<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add no_show, not_reachable, not_interested to status enum.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE recruitment_candidates MODIFY COLUMN status ENUM('active', 'hired', 'rejected', 'withdrawn', 'no_show', 'not_reachable', 'not_interested') NOT NULL DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE recruitment_candidates MODIFY COLUMN status ENUM('active', 'hired', 'rejected', 'withdrawn') NOT NULL DEFAULT 'active'");
    }
};
