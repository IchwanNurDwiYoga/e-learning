<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LearningGroup;
use App\Models\Task;
use App\Models\TaskAssessment;
use App\Models\TaskSubmission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    /**
     * Show student dashboard with task timeline and submission access.
     */
    public function dashboard(Request $request): Response
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return Inertia::render('Dashboard', [
                'tasks' => [
                    'ongoing' => [],
                    'upcoming' => [],
                    'completed' => [],
                ],
            ]);
        }

        $groups = $user->learningGroups()->with('course')->get();
        $groupIds = $groups->pluck('id');
        $courseIds = $groups->pluck('course_id')->unique()->values();

        $submissionsByTask = collect();
        if ($groupIds->isNotEmpty()) {
            $submissionsByTask = TaskSubmission::whereIn('learning_group_id', $groupIds)
                ->with('submittedBy')
                ->get()
                ->groupBy('task_id')
                ->map(function ($submissions) {
                    return $submissions->sortBy('created_at')->values();
                });
        }

        $tasks = Task::query()
            ->whereIn('course_id', $courseIds)
            ->with('course:id,title')
            ->get()
            ->map(function (Task $task) use ($submissionsByTask, $groups, $user) {
                $now = now();
                $startsAt = $task->start_date;
                $deadline = $task->deadline;

                $isUpcoming = $startsAt && $startsAt->gt($now);
                $isCompleted = $deadline && $deadline->lt($now);
                $isOngoing = !$isUpcoming && !$isCompleted;

                $learningGroup = $groups->firstWhere('task_id', $task->id)
                    ?? $groups->first(function ($group) use ($task) {
                        return !$group->task_id && $group->course_id === $task->course_id;
                    });
                $pivotRole = $learningGroup?->pivot?->role;
                $isLeader = $pivotRole === 'leader';
                $taskSubmissions = $submissionsByTask->get($task->id, collect());
                $firstSubmission = $taskSubmissions->firstWhere('submission_label', TaskSubmission::LABEL_FIRST_SUBMIT);
                $finalSubmission = $taskSubmissions->firstWhere('submission_label', TaskSubmission::LABEL_FINAL_SUBMIT);
                $existingSubmission = $finalSubmission ?? $taskSubmissions->last();

                $allTaskSubmissions = TaskSubmission::query()
                    ->where('task_id', $task->id)
                    ->get(['learning_group_id', 'submission_label']);

                $courseGroups = LearningGroup::query()
                    ->where(function ($query) use ($task) {
                        $query->where('task_id', $task->id)
                            ->orWhere(function ($legacyQuery) use ($task) {
                                $legacyQuery->whereNull('task_id')->where('course_id', $task->course_id);
                            });
                    })
                    ->get(['id', 'name'])
                    ->map(function ($group) use ($allTaskSubmissions) {
                        return [
                            'id' => $group->id,
                            'name' => $group->name,
                            'first_submission' => $allTaskSubmissions->contains(function ($submission) use ($group) {
                                return (int) $submission->learning_group_id === (int) $group->id
                                    && $submission->submission_label === TaskSubmission::LABEL_FIRST_SUBMIT;
                            }),
                            'final_submission' => $allTaskSubmissions->contains(function ($submission) use ($group) {
                                return (int) $submission->learning_group_id === (int) $group->id
                                    && $submission->submission_label === TaskSubmission::LABEL_FINAL_SUBMIT;
                            }),
                        ];
                    });

                $assessmentStatuses = TaskAssessment::where('task_id', $task->id)
                    ->where('assessor_group_id', $learningGroup?->id)
                    ->get()
                    ->map(function ($assessment) {
                        return [
                            'assessment_scope' => $assessment->assessment_scope,
                            'assessment_type' => $assessment->assessment_type,
                            'submission_stage' => $assessment->submission_stage,
                            'target_group_id' => $assessment->target_group_id,
                            'total_score' => $assessment->total_score,
                            'average_score' => $assessment->average_score,
                            'created_at' => $assessment->created_at,
                        ];
                    })
                    ->values();

                $mapAssessmentDetail = function (TaskAssessment $assessment) {
                    $scores = $assessment->rubric_scores;
                    if (!is_array($scores) || count($scores) === 0) {
                        $scores = [
                            $assessment->score_1,
                            $assessment->score_2,
                            $assessment->score_3,
                        ];
                    }

                    $comments = $assessment->rubric_comments;
                    if (!is_array($comments)) {
                        $comments = [
                            $assessment->comment_1,
                            $assessment->comment_2,
                            $assessment->comment_3,
                        ];
                    }

                    return [
                        'id' => $assessment->id,
                        'assessment_scope' => $assessment->assessment_scope,
                        'assessment_type' => $assessment->assessment_type,
                        'submission_stage' => $assessment->submission_stage,
                        'assessment_date' => $assessment->assessment_date,
                        'class_name' => $assessment->class_name,
                        'assessor_name' => $assessment->assessor_name,
                        'assessor_group' => [
                            'id' => $assessment->assessorGroup?->id,
                            'name' => $assessment->assessorGroup?->name,
                        ],
                        'target_group' => [
                            'id' => $assessment->targetGroup?->id,
                            'name' => $assessment->targetGroup?->name,
                        ],
                        'scores' => array_values($scores),
                        'comments' => array_values($comments),
                        'total_score' => $assessment->total_score,
                        'average_score' => $assessment->average_score,
                    ];
                };

                $submittedAssessments = TaskAssessment::with(['assessorGroup:id,name', 'targetGroup:id,name'])
                    ->where('task_id', $task->id)
                    ->where('assessor_group_id', $learningGroup?->id)
                    ->get()
                    ->map($mapAssessmentDetail)
                    ->values();

                $receivedPeerAssessments = TaskAssessment::with(['assessorGroup:id,name', 'targetGroup:id,name'])
                    ->where('task_id', $task->id)
                    ->where('target_group_id', $learningGroup?->id)
                    ->where('assessment_scope', TaskAssessment::SCOPE_PEER_GROUP)
                    ->where('assessor_group_id', '!=', $learningGroup?->id)
                    ->get()
                    ->map($mapAssessmentDetail)
                    ->values();

                $receivedTeacherAssessments = TaskAssessment::with(['assessorGroup:id,name', 'targetGroup:id,name'])
                    ->where('task_id', $task->id)
                    ->where('target_group_id', $learningGroup?->id)
                    ->where('assessment_scope', TaskAssessment::SCOPE_TEACHER)
                    ->get()
                    ->map($mapAssessmentDetail)
                    ->values();

                $mapSubmission = function ($submission) {
                    if (!$submission) {
                        return null;
                    }

                    $taskFilePath = $submission->task_file_path ?: $submission->file_path;
                    $productFilePath = $submission->product_file_path;

                    return [
                        'id' => $submission->id,
                        'submission_label' => $submission->submission_label,
                        'description' => $submission->description,
                        'file_path' => $taskFilePath,
                        'task_file_path' => $taskFilePath,
                        'product_file_path' => $productFilePath,
                        'task_file_name' => $taskFilePath ? basename($taskFilePath) : null,
                        'product_file_name' => $productFilePath ? basename($productFilePath) : null,
                        'status' => $submission->status,
                        'teacher_notes' => $submission->teacher_notes,
                        'submitted_by' => [
                            'id' => $submission->submittedBy?->id,
                            'name' => $submission->submittedBy?->name,
                        ],
                        'updated_at' => $submission->updated_at,
                    ];
                };

                return [
                    'id' => $task->id,
                    'label' => $task->label,
                    'title' => $task->title,
                    'description' => $task->description,
                    'file_path' => $task->file_path,
                    'start_date' => $task->start_date,
                    'deadline' => $task->deadline,
                    'course' => [
                        'id' => $task->course?->id,
                        'title' => $task->course?->title,
                    ],
                    'status_group' => $isUpcoming ? 'upcoming' : ($isCompleted ? 'completed' : 'ongoing'),
                    'can_access' => !$isUpcoming,
                    'can_submit' => $isOngoing && $isLeader && !$finalSubmission,
                    'is_leader' => $isLeader,
                    'submission_attempts' => $taskSubmissions->count(),
                    'is_final_submission_stage' => $isOngoing && $isLeader && $firstSubmission && !$finalSubmission,
                    'learning_group' => $learningGroup ? [
                        'id' => $learningGroup->id,
                        'name' => $learningGroup->name,
                    ] : null,
                    'course_groups' => $courseGroups,
                    'assessor_name' => $learningGroup ? ($user->name.' - '.$learningGroup->name) : null,
                    'assessment_statuses' => $assessmentStatuses,
                    'submitted_assessments' => $submittedAssessments,
                    'received_peer_assessments' => $receivedPeerAssessments,
                    'received_teacher_assessments' => $receivedTeacherAssessments,
                    'existing_submission' => $mapSubmission($existingSubmission),
                    'first_submission' => $mapSubmission($firstSubmission),
                    'final_submission' => $mapSubmission($finalSubmission),
                ];
            });

        return Inertia::render('Dashboard', [
            'tasks' => [
                'ongoing' => $tasks->where('status_group', 'ongoing')->sortBy('deadline')->values(),
                'upcoming' => $tasks->where('status_group', 'upcoming')->sortBy('start_date')->values(),
                'completed' => $tasks->where('status_group', 'completed')->sortByDesc('deadline')->values(),
            ],
        ]);
    }

    /**
     * Show all courses a student is enrolled in
     */
    public function indexCourses(Request $request): Response
    {
        $user = $request->user();

        // Get unique courses from learning groups where user is a member
        $courses = Course::whereHas('learningGroups.members', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })
            ->with([
                'teacher:id,name',
                'learningGroups' => function ($query) use ($user) {
                    $query->whereHas('members', function ($memberQuery) use ($user) {
                        $memberQuery->where('users.id', $user->id);
                    });
                },
            ])
            ->withCount('learningGroups')
            ->get();

        return Inertia::render('Student/Courses', [
            'courses' => $courses,
        ]);
    }

    /**
     * Show course detail with learning groups and members
     */
    public function showCourse(Request $request, Course $course): Response
    {
        $user = $request->user();

        // Check if student is enrolled in this course
        $isEnrolled = $course->learningGroups()
            ->whereHas('members', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->exists();

        if (!$isEnrolled) {
            abort(403, 'You are not enrolled in this course');
        }

        $course->load([
            'teacher:id,name',
            'learningGroups' => function ($query) use ($user) {
                $query->with([
                    'members' => function ($memberQuery) {
                        $memberQuery->select('users.id', 'name', 'username');
                    }
                ])->whereHas('members', function ($memberQuery) use ($user) {
                    $memberQuery->where('users.id', $user->id);
                });
            },
            'tasks' => function ($query) {
                $query->latest();
            },
        ]);

        return Inertia::render('Student/CourseDetail', [
            'course' => $course,
        ]);
    }
}

