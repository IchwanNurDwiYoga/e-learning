<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assessor_group_id')->constrained('learning_groups')->cascadeOnDelete();
            $table->foreignId('target_group_id')->constrained('learning_groups')->cascadeOnDelete();
            $table->enum('assessment_scope', ['personal_group', 'peer_group']);
            $table->enum('assessment_type', ['task', 'product', 'product_presentation']);
            $table->date('assessment_date');
            $table->string('class_name');
            $table->string('assessor_name');
            $table->unsignedTinyInteger('score_1');
            $table->unsignedTinyInteger('score_2');
            $table->unsignedTinyInteger('score_3');
            $table->text('comment_1')->nullable();
            $table->text('comment_2')->nullable();
            $table->text('comment_3')->nullable();
            $table->unsignedSmallInteger('total_score');
            $table->decimal('average_score', 5, 2);
            $table->timestamps();

            $table->unique([
                'task_id',
                'assessor_group_id',
                'assessment_scope',
                'assessment_type',
            ], 'task_assessment_unique_once');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assessments');
    }
};
