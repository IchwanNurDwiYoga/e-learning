<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Course;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Course $course)
    {
        return Inertia::render('Teacher/CreateTask', [
            'course' => $course,
            'taskOptions' => Task::topicOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $validated = $request->validated();

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('tasks', $fileName, 'public');
            $validated['file_path'] = $filePath;
        }

        Task::create($validated);

        return redirect()->route('teacher.courses.show', $request->course_id)
            ->with('success', 'Task created successfully!');
    }

    /**
     * Download task file with authorization
     */
    public function download(Task $task)
    {
        $course = $task->course;
        $user = auth()->user();

        $isTeacherOwner = $course->teacher_id === $user->id;
        $isStudentMember = $user->isStudent() && $course->learningGroups()
            ->whereHas('members', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();

        if (!$isTeacherOwner && !$isStudentMember) {
            abort(403, 'Unauthorized to download this file.');
        }

        if ($isStudentMember && $task->start_date && $task->start_date->isFuture()) {
            abort(403, 'Task file can only be accessed when task time has started.');
        }

        if (!$task->file_path || !Storage::disk('public')->exists($task->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download($task->file_path);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        //
    }
}
