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
        Schema::table('recruitment_job_posts', function (Blueprint $table) {
            $table->string('unique_id', 32)->unique()->nullable()->after('id');
            $table->index('unique_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_job_posts', function (Blueprint $table) {
            $table->dropIndex(['unique_id']);
            $table->dropColumn('unique_id');
        });
    }
};
