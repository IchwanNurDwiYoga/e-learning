<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningGroup extends Model
{
    /** @use HasFactory<\Database\Factories\LearningGroupFactory> */
    use HasFactory;

    protected $fillable = ['name', 'subject_id', 'created_by', 'course_id', 'task_id'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'learning_group_members')
            ->withPivot(['id', 'role'])
            ->withTimestamps();
    }
}
