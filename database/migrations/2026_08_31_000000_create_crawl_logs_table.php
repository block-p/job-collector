<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crawl_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id')->constrained('platform_postings')->cascadeOnDelete();
            $table->string('platform_title')->nullable();
            $table->string('status', 20)->default('running');
            $table->integer('jobs_count')->default(0);
            $table->integer('pages_count')->default(0);
            $table->text('error')->nullable();
            $table->json('filters')->default('{}');
            $table->integer('duration_ms')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crawl_logs');
    }
};
