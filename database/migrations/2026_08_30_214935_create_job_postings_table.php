<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId("platform_id")
                ->constrained('platform_postings')
                ->cascadeOnDelete();
            $table->text("title");
            $table->text("company")->nullable();
            $table->text("salary")->nullable();
            $table->text("location")->nullable();
            $table->text("url");
            $table->text("contract")->nullable();
            $table->json("skills")->nullable();
            $table->unique(['url', 'platform_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
