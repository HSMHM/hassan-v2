<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('cover_image_en')->nullable()->after('cover_image');
        });

        Schema::table('workshops', function (Blueprint $table) {
            $table->string('cover_image_en')->nullable()->after('cover_image');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('cover_image_en');
        });

        Schema::table('workshops', function (Blueprint $table) {
            $table->dropColumn('cover_image_en');
        });
    }
};
