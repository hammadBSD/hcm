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
            $table->foreign('default_pipeline_id')
                ->references('id')
                ->on('recruitment_pipelines')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_job_posts', function (Blueprint $table) {
            $table->dropForeign(['default_pipeline_id']);
        });
    }
};
