<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('task_assessments', 'submission_stage')) {
            Schema::table('task_assessments', function (Blueprint $table) {
                $table->enum('submission_stage', ['first_submit', 'final_submit'])
                    ->default('final_submit')
                    ->after('assessment_type');
            });
        }

        // Drop old unique constraint and recreate with submission_stage included
        if (!$this->indexExists('task_assessments', 'task_assessments_task_id_idx')) {
            Schema::table('task_assessments', function (Blueprint $table) {
                $table->index('task_id', 'task_assessments_task_id_idx');
            });
        }

        if ($this->indexExists('task_assessments', 'task_assessment_unique_once')) {
            Schema::table('task_assessments', function (Blueprint $table) {
                $table->dropUnique('task_assessment_unique_once');
            });
        }

        Schema::table('task_assessments', function (Blueprint $table) {
            $table->unique(
                ['task_id', 'assessor_group_id', 'assessment_scope', 'assessment_type', 'submission_stage'],
                'task_assessment_unique_once'
            );
        });
    }

    public function down(): void
    {
        if ($this->indexExists('task_assessments', 'task_assessment_unique_once')) {
            Schema::table('task_assessments', function (Blueprint $table) {
                $table->dropUnique('task_assessment_unique_once');
            });
        }

        Schema::table('task_assessments', function (Blueprint $table) {
            $table->unique(
                ['task_id', 'assessor_group_id', 'assessment_scope', 'assessment_type'],
                'task_assessment_unique_once'
            );
        });

        if (Schema::hasColumn('task_assessments', 'submission_stage')) {
            Schema::table('task_assessments', function (Blueprint $table) {
                $table->dropColumn('submission_stage');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $row = DB::selectOne(
            'SELECT COUNT(1) AS cnt FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [DB::getDatabaseName(), $table, $indexName]
        );

        return (int) ($row->cnt ?? 0) > 0;
    }
};
