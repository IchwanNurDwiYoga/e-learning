<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('label')->nullable()->after('course_id');
        });

        $taskNumberByCourse = [];

        DB::table('tasks')
            ->orderBy('course_id')
            ->orderBy('id')
            ->get(['id', 'course_id'])
            ->each(function ($task) use (&$taskNumberByCourse) {
                $taskNumberByCourse[$task->course_id] = ($taskNumberByCourse[$task->course_id] ?? 0) + 1;

                DB::table('tasks')
                    ->where('id', $task->id)
                    ->update([
                        'label' => 'Task '.$taskNumberByCourse[$task->course_id],
                    ]);
            });

        Schema::table('tasks', function (Blueprint $table) {
            $table->string('label')->nullable(false)->change();
            $table->unique(['course_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropUnique('tasks_course_id_label_unique');
            $table->dropColumn('label');
        });
    }
};
