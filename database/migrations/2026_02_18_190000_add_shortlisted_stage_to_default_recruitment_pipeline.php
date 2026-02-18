<?php

use App\Models\Recruitment\Pipeline;
use App\Models\Recruitment\PipelineStage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds "Shortlisted" stage before "Offer" in the default recruitment pipeline.
     */
    public function up(): void
    {
        $pipeline = Pipeline::where('is_default', true)->first();
        if (!$pipeline) {
            return;
        }

        $hasShortlisted = PipelineStage::where('pipeline_id', $pipeline->id)
            ->where('name', 'Shortlisted')
            ->exists();
        if ($hasShortlisted) {
            return;
        }

        $offerStage = PipelineStage::where('pipeline_id', $pipeline->id)
            ->where('name', 'Offer')
            ->first();
        $hiredStage = PipelineStage::where('pipeline_id', $pipeline->id)
            ->where('name', 'Hired')
            ->first();

        $shortlistedOrder = $offerStage ? $offerStage->order : 4;

        PipelineStage::create([
            'pipeline_id' => $pipeline->id,
            'name' => 'Shortlisted',
            'color' => 'indigo',
            'order' => $shortlistedOrder,
            'is_default' => false,
        ]);

        if ($offerStage) {
            $offerStage->update(['order' => $shortlistedOrder + 1]);
        }
        if ($hiredStage) {
            $hiredStage->update(['order' => $shortlistedOrder + 2]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $pipeline = Pipeline::where('is_default', true)->first();
        if (!$pipeline) {
            return;
        }

        $shortlisted = PipelineStage::where('pipeline_id', $pipeline->id)
            ->where('name', 'Shortlisted')
            ->first();
        if (!$shortlisted) {
            return;
        }

        $shortlisted->delete();

        $offerStage = PipelineStage::where('pipeline_id', $pipeline->id)->where('name', 'Offer')->first();
        $hiredStage = PipelineStage::where('pipeline_id', $pipeline->id)->where('name', 'Hired')->first();
        if ($offerStage) {
            $offerStage->update(['order' => 4]);
        }
        if ($hiredStage) {
            $hiredStage->update(['order' => 5]);
        }
    }
};
