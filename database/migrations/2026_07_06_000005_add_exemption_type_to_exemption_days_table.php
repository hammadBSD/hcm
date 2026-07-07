<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exemption_days', function (Blueprint $table) {
            $table->string('exemption_type', 16)->default('n/a')->after('scope_type');
        });

        DB::table('exemption_days')->update(['exemption_type' => 'lates']);
    }

    public function down(): void
    {
        Schema::table('exemption_days', function (Blueprint $table) {
            $table->dropColumn('exemption_type');
        });
    }
};
