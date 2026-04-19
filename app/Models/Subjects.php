<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subjects extends Model
{
    /** @use HasFactory<\Database\Factories\SubjectsFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'subject_name'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
