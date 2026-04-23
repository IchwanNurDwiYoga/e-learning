<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAssessment;
use App\Models\TaskSubmission;
use App\Models\LearningGroup;
use App\Http\Requests\StoreTaskSubmissionRequest;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TaskSubmissionController extends Controller
{
    /**
     * Show task detail with submissions
     */
    public function show(Task $task)
    {
        $user = auth()->user();
        $course = $task->course;

        // Only allow teacher who created the course
        if ($course->teacher_id !== $user->id) {
            abort(403, 'Unauthorized to view this task.');
        }

        $submissions = $task->submissions()
            ->with([
                'learningGroup',
                'submittedBy',
            ])
            ->latest()
            ->get();

        // Teacher assessments for this task, keyed by target_group_id + type
        $teacherAssessments = TaskAssessment::where('task_id', $task->id)
            ->where('assessment_scope', TaskAssessment::SCOPE_TEACHER)
            ->where('assessor_id', $user->id)
            ->get()
            ->map(function ($assessment) {
                $scores = is_array($assessment->rubric_scores) && count($assessment->rubric_scores)
                    ? $assessment->rubric_scores
                    : array_filter([$assessment->score_1, $assessment->score_2, $assessment->score_3]);

                $comments = is_array($assessment->rubric_comments)
                    ? $assessment->rubric_comments
                    : array_filter([$assessment->comment_1, $assessment->comment_2, $assessment->comment_3]);

                return [
                    'id' => $assessment->id,
                    'target_group_id' => $assessment->target_group_id,
                    'assessment_type' => $assessment->assessment_type,
                    'scores' => array_values($scores),
                    'comments' => array_values($comments),
                    'total_score' => $assessment->total_score,
                    'average_score' => $assessment->average_score,
                    'assessment_date' => $assessment->assessment_date,
                ];
            })
            ->values();

        $mapSubmission = function ($submission) {
            if (!$submission) {
                return null;
            }

            return [
                'id' => $submission->id,
                'task_id' => $submission->task_id,
                'learning_group_id' => $submission->learning_group_id,
                'learning_group' => $submission->learningGroup,
                'submitted_by' => $submission->submittedBy,
                'submission_label' => $submission->submission_label,
                'description' => $submission->description,
                'file_path' => $submission->file_path,
                'status' => $submission->status,
                'teacher_notes' => $submission->teacher_notes,
                'created_at' => $submission->created_at,
                'updated_at' => $submission->updated_at,
            ];
        };

        $enrichedSubmissions = $submissions
            ->groupBy('learning_group_id')
            ->map(function ($groupSubmissions, $groupId) use ($teacherAssessments, $mapSubmission) {
                $finalSubmission = $groupSubmissions->firstWhere('submission_label', TaskSubmission::LABEL_FINAL_SUBMIT);
                $firstSubmission = $groupSubmissions->firstWhere('submission_label', TaskSubmission::LABEL_FIRST_SUBMIT);
                $displaySubmission = $finalSubmission ?? $groupSubmissions->first();

                $groupTeacherAssessments = $teacherAssessments
                    ->where('target_group_id', (int) $groupId)
                    ->values();

                return array_merge($mapSubmission($displaySubmission), [
                    'first_submission' => $mapSubmission($firstSubmission),
                    'final_submission' => $mapSubmission($finalSubmission),
                    'has_teacher_assessments' => $groupTeacherAssessments->isNotEmpty(),
                    'teacher_assessments' => $groupTeacherAssessments,
                ]);
            })
            ->sortByDesc('created_at')
            ->values();

        return Inertia::render('Teacher/TaskDetail', [
            'task' => $task,
            'course' => $course,
            'submissions' => $enrichedSubmissions,
            'teacher_assessments' => $teacherAssessments,
        ]);
    }

    /**
     * Store task submission from team leader
     */
    public function store(StoreTaskSubmissionRequest $request, Task $task)
    {
        $user = auth()->user();

        $learningGroup = LearningGroup::where('course_id', $task->course_id)
            ->whereHas('members', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->first();

        if (!$learningGroup) {
            return redirect()->back()->with('error', 'You are not assigned to a group in this course.');
        }

        if ($task->start_date && $task->start_date->isFuture()) {
            return redirect()->back()->with('error', 'Task submission is not open yet.');
        }

        if ($task->deadline && $task->deadline->isPast()) {
            return redirect()->back()->with('error', 'Deadline has passed. You can no longer upload this task.');
        }

        $memberRole = $learningGroup->members()
            ->where('user_id', $user->id)
            ->first();

        if (!$memberRole || $memberRole->pivot->role !== 'leader') {
            return redirect()->back()->with('error', 'Only team leader can submit task.');
        }

        $submissions = TaskSubmission::where('task_id', $task->id)
            ->where('learning_group_id', $learningGroup->id)
            ->orderBy('created_at')
            ->get();

        $firstSubmission = $submissions->firstWhere('submission_label', TaskSubmission::LABEL_FIRST_SUBMIT);
        $finalSubmission = $submissions->firstWhere('submission_label', TaskSubmission::LABEL_FINAL_SUBMIT);

        if ($finalSubmission) {
            return redirect()->back()->with('error', 'Final submission sudah dilakukan. Upload tidak bisa dilakukan lagi.');
        }

        $isFinalSubmission = $firstSubmission !== null;

        if ($isFinalSubmission && !$request->boolean('confirm_final_submission')) {
            return redirect()->back()->with('error', 'Final submission membutuhkan konfirmasi.');
        }

        $submissionLabel = $isFinalSubmission
            ? TaskSubmission::LABEL_FINAL_SUBMIT
            : TaskSubmission::LABEL_FIRST_SUBMIT;

        $validated = $request->validated();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $this->buildSubmissionFileName($file, $isFinalSubmission);
            $filePath = $file->storeAs("task_submissions/task_{$task->id}", $fileName, 'public');
            $validated['file_path'] = $filePath;
        }

        TaskSubmission::create([
            'task_id' => $task->id,
            'learning_group_id' => $learningGroup->id,
            'submitted_by' => $user->id,
            'submission_label' => $submissionLabel,
            'description' => $validated['description'] ?? null,
            'file_path' => $validated['file_path'] ?? null,
            'status' => 'submitted',
        ]);

        $message = $isFinalSubmission
            ? 'Final submission berhasil diunggah.'
            : 'First submission berhasil diunggah.';

        return redirect()->back()->with('success', $message);
    }

    private function buildSubmissionFileName(UploadedFile $file, bool $isFinalSubmission): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();

        if ($isFinalSubmission && !Str::contains(Str::lower($originalName), 'revisi')) {
            $originalName .= '_revisi';
        }

        $baseName = Str::slug($originalName, '_');
        if ($baseName === '') {
            $baseName = 'submission';
        }

        return time().'_'.$baseName.'.'.$extension;
    }

    /**
     * Download submission file
     */
    public function downloadSubmission(TaskSubmission $submission)
    {
        $user = auth()->user();
        $task = $submission->task;
        $course = $task->course;

        // Check authorization
        if ($course->teacher_id !== $user->id) {
            // Check if user is member of the submitting group
            $isMember = $submission->learningGroup->members()
                ->where('user_id', $user->id)
                ->exists();

            if (!$isMember) {
                abort(403, 'Unauthorized to download this file.');
            }
        }

        if (!$submission->file_path || !Storage::disk('public')->exists($submission->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download($submission->file_path);
    }

    /**
     * Update submission status
     */
    public function updateStatus(Request $request, TaskSubmission $submission)
    {
        $user = auth()->user();
        $course = $submission->task->course;

        // Only teacher can update status
        if ($course->teacher_id !== $user->id) {
            abort(403, 'Unauthorized to update submission.');
        }

        $validated = $request->validate([
            'status' => 'required|in:submitted,reviewed,returned',
            'teacher_notes' => 'nullable|string',
        ]);

        $submission->update($validated);

        return redirect()->back()->with('success', 'Submission updated successfully!');
    }
}
