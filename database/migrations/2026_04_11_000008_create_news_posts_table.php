<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_posts', function (Blueprint $table) {
            $table->id();

            $table->string('source_url')->nullable();
            $table->string('source_title')->nullable();
            $table->string('source_type')->nullable();

            $table->string('title_ar');
            $table->string('title_en');
            $table->string('slug_ar')->unique();
            $table->string('slug_en')->unique();
            $table->text('excerpt_ar');
            $table->text('excerpt_en');
            $table->longText('content_ar');
            $table->longText('content_en');

            $table->text('social_post_ar');
            $table->text('social_post_en');

            $table->string('meta_title_ar')->nullable();
            $table->string('meta_title_en')->nullable();
            $table->text('meta_description_ar')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('og_image')->nullable();

            $table->json('references')->nullable();

            $table->enum('status', [
                'draft',
                'pending',
                'approved',
                'publishing',
                'published',
                'partial',
                'skipped',
                'failed',
            ])->default('draft');

            $table->json('platform_status')->nullable();

            $table->timestamp('sent_to_whatsapp_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_posts');
    }
};
