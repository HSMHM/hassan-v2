<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Structured bilingual extras:
            // {
            //   "reading_time_ar": "6 دقائق", "reading_time_en": "6 min read",
            //   "takeaways_ar":    [...],    "takeaways_en":    [...],
            //   "tags_ar":         [...],    "tags_en":         [...],
            //   "references":      [{"title": "...", "url": "..."}]
            // }
            $table->json('extras')->nullable()->after('cover_image_en');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('extras');
        });
    }
};
