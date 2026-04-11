<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->string('category_en')->nullable()->after('category');
        });

        Schema::table('workshops', function (Blueprint $table) {
            $table->string('platform_en')->nullable()->after('platform');
        });
    }

    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropColumn('category_en');
        });

        Schema::table('workshops', function (Blueprint $table) {
            $table->dropColumn('platform_en');
        });
    }
};
