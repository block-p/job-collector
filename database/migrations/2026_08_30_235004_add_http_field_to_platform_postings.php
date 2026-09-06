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
        Schema::table('platform_postings', function (Blueprint $table) {
            $table->string('method',10)->default('GET')->after('id');
            $table->text('endpoint')->nullable();
            $table->jsonb('headers')->default('{}');
            $table->jsonb('query_params')->default('{}');
            $table->jsonb('body_template')->default('{}');
            $table->jsonb('pagination')->default('{}');
            $table->jsonb('response_mapping')->default('{}');
            $table->integer('delay_ms')->default(1000);
            $table->string('last_status',50)->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('last_crawled_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('platform_postings', function (Blueprint $table) {
                $table->dropColumn([
                    'method',
                    'endpoint',
                    'headers',
                    'query_params',
                    'body_template',
                    'pagination',
                    'response_mapping',
                    'delay_ms',
                    'last_status',
                    'last_error',
                    'last_crawled_at',
                ]);
            });
    }
};
