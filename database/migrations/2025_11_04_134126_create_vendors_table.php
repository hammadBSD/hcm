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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->foreignId('province_id')->nullable()->constrained('provinces')->onDelete('set null');
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('tax_id')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            $table->index('code');
            $table->index('country_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
