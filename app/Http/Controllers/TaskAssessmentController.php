<?php

namespace App\Http\Controllers;

use App\Models\LearningGroup;
use App\Models\Task;
use App\Models\TaskAssessment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskAssessmentController extends Controller
{
    /**
     * Teacher submits assessment for a group's work.
     */
    public function storeTeacher(Request $request, Task $task): RedirectResponse
    {
        $teacher = $request->user();

        if (!$teacher || !$teacher->isTeacher()) {
            abort(403, 'Unauthorized.');
        }

        if ($task->course->teacher_id !== $teacher->id) {
            abort(403, 'Anda bukan teacher dari course ini.');
        }

        $validated = $request->validate([
            'assessment_type' => ['required', Rule::in([TaskAssessment::TYPE_TASK, TaskAssessment::TYPE_PRODUCT, TaskAssessment::TYPE_PRODUCT_PRESENTATION])],
            'target_group_id' => ['required', 'integer', 'exists:learning_groups,id'],
            'scores' => ['required', 'array', 'min:1'],
            'scores.*' => ['required', 'integer', 'between:1,4'],
            'comments' => ['nullable', 'array'],
            'comments.*' => ['nullable', 'string'],
            'confirm_irreversible' => ['required', 'accepted'],
        ], [
            'confirm_irreversible.accepted' => 'Konfirmasi wajib disetujui karena asesmen hanya bisa dikirim sekali.',
        ]);

        $targetGroup = LearningGroup::where('id', $validated['target_group_id'])
            ->where('course_id', $task->course_id)
            ->first();

        if (!$targetGroup) {
            return redirect()->back()->with('error', 'Kelompok tujuan asesmen tidak valid untuk course ini.');
        }

        // Check group has final submission
        if (!$task->submissions()
            ->where('learning_group_id', $targetGroup->id)
            ->where('submission_label', 'final_submit')
            ->exists()) {
            return redirect()->back()->with('error', 'Asesmen hanya bisa diisi setelah kelompok melakukan final submission.');
        }

        // One-time per teacher per group per type
        $alreadySubmitted = TaskAssessment::where('task_id', $task->id)
            ->where('assessor_id', $teacher->id)
            ->where('target_group_id', $targetGroup->id)
            ->where('assessment_scope', TaskAssessment::SCOPE_TEACHER)
            ->where('assessment_type', $validated['assessment_type'])
            ->exists();

        if ($alreadySubmitted) {
            return redirect()->back()->with('error', 'Asesmen ini sudah pernah dikirim dan tidak bisa diubah.');
        }

        $scores = array_map('intval', $validated['scores']);
        $comments = $validated['comments'] ?? [];
        $indicatorCount = count($scores);
        $totalScore = array_sum($scores);
        $averageScore = $indicatorCount > 0 ? round($totalScore / $indicatorCount, 2) : 0;

        TaskAssessment::create([
            'task_id' => $task->id,
            'assessor_id' => $teacher->id,
            'assessor_group_id' => null,
            'target_group_id' => $targetGroup->id,
            'assessment_scope' => TaskAssessment::SCOPE_TEACHER,
            'assessment_type' => $validated['assessment_type'],
            'assessment_date' => now()->toDateString(),
            'class_name' => $task->course?->title ?? '-',
            'assessor_name' => $teacher->name.' (Guru)',
            'rubric_scores' => $scores,
            'rubric_comments' => array_values($comments),
            'indicator_count' => $indicatorCount,
            'score_1' => $scores[0] ?? 1,
            'score_2' => $scores[1] ?? 1,
            'score_3' => $scores[2] ?? 1,
            'comment_1' => $comments[0] ?? null,
            'comment_2' => $comments[1] ?? null,
            'comment_3' => $comments[2] ?? null,
            'total_score' => $totalScore,
            'average_score' => $averageScore,
        ]);

        return redirect()->back()->with('success', 'Asesmen guru berhasil disimpan.');
    }

    /**
     * Student submits assessment (self or peer).
     */
    public function store(Request $request, Task $task): RedirectResponse
    {
        $user = $request->user();

        if (!$user || !$user->isStudent()) {
            abort(403, 'Unauthorized.');
        }

        $assessorGroup = LearningGroup::where('course_id', $task->course_id)
            ->whereHas('members', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->first();

        if (!$assessorGroup) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar pada kelompok untuk course ini.');
        }

        if (!$task->submissions()
            ->where('learning_group_id', $assessorGroup->id)
            ->where('submission_label', 'final_submit')
            ->exists()) {
            return redirect()->back()->with('error', 'Asesmen hanya bisa diisi setelah final submission.');
        }

        $validated = $request->validate([
            'assessment_scope' => ['required', Rule::in([TaskAssessment::SCOPE_PERSONAL_GROUP, TaskAssessment::SCOPE_PEER_GROUP])],
            'assessment_type' => ['required', Rule::in([TaskAssessment::TYPE_TASK, TaskAssessment::TYPE_PRODUCT, TaskAssessment::TYPE_PRODUCT_PRESENTATION])],
            'target_group_id' => ['required', 'integer', 'exists:learning_groups,id'],
            'scores' => ['required', 'array', 'min:1'],
            'scores.*' => ['required', 'integer', 'between:1,4'],
            'comments' => ['nullable', 'array'],
            'comments.*' => ['nullable', 'string'],
            'confirm_irreversible' => ['required', 'accepted'],
        ], [
            'confirm_irreversible.accepted' => 'Konfirmasi wajib disetujui karena asesmen hanya bisa dikirim sekali.',
        ]);

        $targetGroup = LearningGroup::where('id', $validated['target_group_id'])
            ->where('course_id', $task->course_id)
            ->first();

        if (!$targetGroup) {
            return redirect()->back()->with('error', 'Kelompok tujuan asesmen tidak valid untuk course ini.');
        }

        if ($validated['assessment_scope'] === TaskAssessment::SCOPE_PERSONAL_GROUP && $targetGroup->id !== $assessorGroup->id) {
            return redirect()->back()->with('error', 'Penilaian pribadi hanya boleh untuk kelompok sendiri.');
        }

        if ($validated['assessment_scope'] === TaskAssessment::SCOPE_PEER_GROUP && $targetGroup->id === $assessorGroup->id) {
            return redirect()->back()->with('error', 'Penilaian teman sebaya tidak boleh memilih kelompok sendiri.');
        }

        $alreadySubmitted = TaskAssessment::where('task_id', $task->id)
            ->where('assessor_group_id', $assessorGroup->id)
            ->where('assessment_scope', $validated['assessment_scope'])
            ->where('assessment_type', $validated['assessment_type'])
            ->exists();

        if ($alreadySubmitted) {
            return redirect()->back()->with('error', 'Asesmen ini sudah pernah dikirim dan tidak bisa diubah.');
        }

        $scores = array_map('intval', $validated['scores']);
        $comments = $validated['comments'] ?? [];
        $indicatorCount = count($scores);
        $totalScore = array_sum($scores);
        $averageScore = $indicatorCount > 0 ? round($totalScore / $indicatorCount, 2) : 0;

        TaskAssessment::create([
            'task_id' => $task->id,
            'assessor_id' => $user->id,
            'assessor_group_id' => $assessorGroup->id,
            'target_group_id' => $targetGroup->id,
            'assessment_scope' => $validated['assessment_scope'],
            'assessment_type' => $validated['assessment_type'],
            'assessment_date' => now()->toDateString(),
            'class_name' => $task->course?->title ?? '-',
            'assessor_name' => $user->name.' - '.$assessorGroup->name,
            'rubric_scores' => $scores,
            'rubric_comments' => array_values($comments),
            'indicator_count' => $indicatorCount,
            'score_1' => $scores[0] ?? 1,
            'score_2' => $scores[1] ?? 1,
            'score_3' => $scores[2] ?? 1,
            'comment_1' => $comments[0] ?? null,
            'comment_2' => $comments[1] ?? null,
            'comment_3' => $comments[2] ?? null,
            'total_score' => $totalScore,
            'average_score' => $averageScore,
        ]);

        return redirect()->back()->with('success', 'Asesmen berhasil disimpan. Data tidak bisa diubah lagi.');
    }
}
