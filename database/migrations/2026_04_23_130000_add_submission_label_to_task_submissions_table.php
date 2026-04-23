<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_submissions', function (Blueprint $table) {
            $table->string('submission_label')->nullable()->after('submitted_by');
        });

        DB::table('task_submissions')
            ->whereNull('submission_label')
            ->update([
                'submission_label' => 'first_submit',
            ]);

        Schema::table('task_submissions', function (Blueprint $table) {
            $table->string('submission_label')->nullable(false)->change();
            $table->unique(['task_id', 'learning_group_id', 'submission_label'], 'task_group_submission_label_unique');
        });
    }

    public function down(): void
    {
        Schema::table('task_submissions', function (Blueprint $table) {
            $table->dropUnique('task_group_submission_label_unique');
            $table->dropColumn('submission_label');
        });
    }
};
