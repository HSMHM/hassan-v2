<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->string('proposal_id');
            $table->string('customer_name');
            $table->text('description')->nullable();
            $table->string('password');
            $table->json('content')->nullable();
            $table->enum('locale', ['ar', 'en'])->default('ar');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['proposal_id', 'locale']);
            $table->index('proposal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
