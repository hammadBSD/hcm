<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recruitment_settings', function (Blueprint $table) {
            $table->boolean('restrict_job_post_access')
                ->default(false)
                ->after('restrict_applicant_access');
        });
    }

    public function down(): void
    {
        Schema::table('recruitment_settings', function (Blueprint $table) {
            $table->dropColumn('restrict_job_post_access');
        });
    }
};
