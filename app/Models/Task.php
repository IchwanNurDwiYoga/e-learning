<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    public const TOPIC_OPTIONS = [
        [
            'label' => 'Task 1',
            'title' => 'TOPIK PERUBAHAN LINGKUNGAN DAN POLUSI',
        ],
        [
            'label' => 'Task 2',
            'title' => 'TOPIK PEMANASAN GLOBAL DAN DAMPAKNYA',
        ],
        [
            'label' => 'Task 3',
            'title' => 'TOPIK UPAYA PENANGANAN PERUBAHAN LINGKUNGAN',
        ],
    ];

    protected $fillable = [
        'course_id',
        'label',
        'title',
        'description',
        'file_path',
        'start_date',
        'deadline',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'deadline' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(TaskSubmission::class);
    }

    public function learningGroups(): HasMany
    {
        return $this->hasMany(LearningGroup::class);
    }

    public static function topicOptions(): array
    {
        return self::TOPIC_OPTIONS;
    }

    public static function topicTitles(): array
    {
        return array_column(self::TOPIC_OPTIONS, 'title');
    }

    public static function topicLabels(): array
    {
        return array_column(self::TOPIC_OPTIONS, 'label');
    }

    public static function labelForTitle(?string $title): ?string
    {
        foreach (self::TOPIC_OPTIONS as $option) {
            if ($option['title'] === $title) {
                return $option['label'];
            }
        }

        return null;
    }
}
