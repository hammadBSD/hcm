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
        Schema::create('recruitment_pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipeline_id')->constrained('recruitment_pipelines')->onDelete('cascade');
            $table->string('name');
            $table->enum('color', ['blue', 'yellow', 'purple', 'green', 'emerald', 'red', 'orange', 'pink', 'indigo', 'gray'])->default('blue');
            $table->integer('order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->index(['pipeline_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_pipeline_stages');
    }
};
