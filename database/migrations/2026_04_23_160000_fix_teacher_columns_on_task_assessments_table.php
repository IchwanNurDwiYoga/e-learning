<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE task_assessments MODIFY assessor_group_id BIGINT UNSIGNED NULL');
        DB::statement("ALTER TABLE task_assessments MODIFY assessment_scope ENUM('personal_group', 'peer_group', 'teacher') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("DELETE FROM task_assessments WHERE assessment_scope = 'teacher'");
        DB::statement("ALTER TABLE task_assessments MODIFY assessment_scope ENUM('personal_group', 'peer_group') NOT NULL");
        DB::statement('ALTER TABLE task_assessments MODIFY assessor_group_id BIGINT UNSIGNED NOT NULL');
    }
};
