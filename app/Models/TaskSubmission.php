<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskSubmission extends Model
{
    /** @use HasFactory<\Database\Factories\TaskSubmissionFactory> */
    use HasFactory;

    public const LABEL_FIRST_SUBMIT = 'first_submit';
    public const LABEL_FINAL_SUBMIT = 'final_submit';

    protected $fillable = [
        'task_id',
        'learning_group_id',
        'submitted_by',
        'submission_label',
        'description',
        'file_path',
        'status',
        'teacher_notes',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function learningGroup(): BelongsTo
    {
        return $this->belongsTo(LearningGroup::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
