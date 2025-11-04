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
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->json('options'); // Array of poll options
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('allow_multiple_choices')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['active', 'inactive', 'completed'])->default('active');
            $table->timestamps();
            
            $table->index('start_date');
            $table->index('end_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polls');
    }
};
