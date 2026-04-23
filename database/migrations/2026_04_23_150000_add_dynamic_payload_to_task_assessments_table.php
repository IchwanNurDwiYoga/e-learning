<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_assessments', function (Blueprint $table) {
            $table->json('rubric_scores')->nullable()->after('assessor_name');
            $table->json('rubric_comments')->nullable()->after('rubric_scores');
            $table->unsignedSmallInteger('indicator_count')->default(0)->after('rubric_comments');
        });
    }

    public function down(): void
    {
        Schema::table('task_assessments', function (Blueprint $table) {
            $table->dropColumn(['rubric_scores', 'rubric_comments', 'indicator_count']);
        });
    }
};
