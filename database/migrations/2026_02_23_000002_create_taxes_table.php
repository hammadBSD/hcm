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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tax_year');
            $table->decimal('salary_from', 14, 2)->default(0);
            $table->decimal('salary_to', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('exempted_tax_amount', 14, 2)->default(0);
            $table->decimal('additional_tax_amount', 14, 2)->default(0);
            $table->timestamps();

            $table->index('tax_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
