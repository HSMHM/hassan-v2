<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('user_agent')->nullable()->after('ip_address');
            $table->string('locale', 5)->default('ar')->after('user_agent');
            $table->boolean('is_spam')->default(false)->after('is_read');

            $table->index('is_spam');
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropIndex(['is_spam']);
            $table->dropColumn(['user_agent', 'locale', 'is_spam']);
        });
    }
};
