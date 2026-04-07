<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advance_salary_requests', function (Blueprint $table) {
            $table->date('expected_receiving_date')->nullable()->after('expected_payback_date');
            $table->decimal('received_amount', 14, 2)->nullable()->after('confirmed_at');
            $table->timestamp('received_at')->nullable()->after('received_amount');
        });
    }

    public function down(): void
    {
        Schema::table('advance_salary_requests', function (Blueprint $table) {
            $table->dropColumn(['expected_receiving_date', 'received_amount', 'received_at']);
        });
    }
};
