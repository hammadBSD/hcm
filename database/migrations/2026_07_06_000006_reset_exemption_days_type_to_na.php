<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Historical exemption days were punch-processing exemptions (first/last only).
        DB::table('exemption_days')->update(['exemption_type' => 'n/a']);
    }

    public function down(): void
    {
        // No reliable rollback.
    }
};
