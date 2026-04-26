<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAssessment extends Model
{
    use HasFactory;

    public const SCOPE_PERSONAL_GROUP = 'personal_group';
    public const SCOPE_PEER_GROUP = 'peer_group';
    public const SCOPE_TEACHER = 'teacher';

    public const TYPE_TASK = 'task';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_PRODUCT_PRESENTATION = 'product_presentation';

    public const STAGE_FIRST_SUBMIT = 'first_submit';
    public const STAGE_FINAL_SUBMIT = 'final_submit';

    protected $fillable = [
        'task_id',
        'assessor_id',
        'assessor_group_id',
        'target_group_id',
        'assessment_scope',
        'assessment_type',
        'submission_stage',
        'assessment_date',
        'class_name',
        'assessor_name',
        'rubric_scores',
        'rubric_comments',
        'indicator_count',
        'score_1',
        'score_2',
        'score_3',
        'comment_1',
        'comment_2',
        'comment_3',
        'total_score',
        'average_score',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'average_score' => 'decimal:2',
        'rubric_scores' => 'array',
        'rubric_comments' => 'array',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function assessorGroup(): BelongsTo
    {
        return $this->belongsTo(LearningGroup::class, 'assessor_group_id');
    }

    public function targetGroup(): BelongsTo
    {
        return $this->belongsTo(LearningGroup::class, 'target_group_id');
    }
}
