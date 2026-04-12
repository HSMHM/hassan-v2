<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_posts', function (Blueprint $table) {
            $table->string('og_image_en')->nullable()->after('og_image');
            $table->string('tall_image')->nullable()->after('og_image_en');
            $table->string('tall_image_en')->nullable()->after('tall_image');
        });
    }

    public function down(): void
    {
        Schema::table('news_posts', function (Blueprint $table) {
            $table->dropColumn(['og_image_en', 'tall_image', 'tall_image_en']);
        });
    }
};
