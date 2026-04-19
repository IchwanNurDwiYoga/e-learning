<?php

namespace App\Models;

use App\Models\LearningGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningGroupMembers extends Model
{
    /** @use HasFactory<\Database\Factories\LearningGroupMembersFactory> */
    use HasFactory;

protected $fillable = ['learning_group_id', 'user_id', 'role'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function learningGroup()
    {
        return $this->belongsTo(LearningGroup::class, 'learning_group_id');
    }
}
