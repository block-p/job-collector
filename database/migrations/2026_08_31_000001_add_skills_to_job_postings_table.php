<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The base create-table migration already includes `skills`;
        // keep this idempotent so fresh installs don't hit "duplicate column".
        if (! Schema::hasColumn('job_postings', 'skills')) {
            Schema::table('job_postings', function (Blueprint $table) {
                $table->json('skills')->nullable()->after('contract');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('job_postings', 'skills')) {
            Schema::table('job_postings', function (Blueprint $table) {
                $table->dropColumn('skills');
            });
        }
    }
};
