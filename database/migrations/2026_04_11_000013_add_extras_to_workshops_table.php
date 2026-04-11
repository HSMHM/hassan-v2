<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workshops', function (Blueprint $table) {
            // Structured bilingual content: objectives, audience, topics, outcomes
            // Shape: {
            //   "objectives_ar": [...], "objectives_en": [...],
            //   "audience_ar":   [...], "audience_en":   [...],
            //   "topics_ar":     [...], "topics_en":     [...],
            //   "outcomes_ar":   [...], "outcomes_en":   [...],
            //   "duration_ar":   "...", "duration_en":   "..."
            // }
            $table->json('extras')->nullable()->after('platform_en');
        });
    }

    public function down(): void
    {
        Schema::table('workshops', function (Blueprint $table) {
            $table->dropColumn('extras');
        });
    }
};
