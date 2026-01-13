<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL doesn't support modifying enum directly, so we need to alter the column
        DB::statement("ALTER TABLE tasks MODIFY COLUMN frequency ENUM('one-time', 'daily', 'weekly') DEFAULT 'one-time'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE tasks MODIFY COLUMN frequency ENUM('daily', 'weekly') DEFAULT 'daily'");
    }
};
