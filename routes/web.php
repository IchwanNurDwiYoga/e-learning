<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskAssessmentController;
use App\Http\Controllers\TaskSubmissionController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function (Request $request) {
    $user = $request->user();

    if ($user && $user->isTeacher()) {
        return redirect()->route('teacher.dashboard');
    }

    if ($user && !$user->isStudent()) {
        abort(403, 'Unauthorized access');
    }

    return redirect()->route('student.dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Teacher Routes
Route::middleware(['auth', 'role:teacher'])->group(function () {
    Route::get('/teacher/dashboard', [TeacherController::class, 'dashboard'])->name('teacher.dashboard');
    Route::get('/teacher/students', [TeacherController::class, 'showStudents'])->name('teacher.students.index');
    Route::get('/teacher/courses/{course}', [TeacherController::class, 'show'])->name('teacher.courses.show');
    Route::post('/teacher/courses', [TeacherController::class, 'storeCourse'])->name('teacher.courses.store');
    Route::post('/teacher/students', [TeacherController::class, 'storeStudent'])->name('teacher.students.store');
    Route::post('/teacher/learning-groups', [TeacherController::class, 'storeLearningGroup'])->name('teacher.learning-groups.store');
    Route::post('/teacher/learning-groups/{learningGroup}/members', [TeacherController::class, 'storeLearningGroupMember'])->name('teacher.learning-groups.members.store');
    Route::post('/teacher/learning-groups/{learningGroup}/members/{user}/leader', [TeacherController::class, 'setLearningGroupLeader'])->name('teacher.learning-groups.members.leader');

    // Task Routes
    Route::get('/teacher/courses/{course}/tasks/create', [TaskController::class, 'create'])->name('teacher.tasks.create');
    Route::post('/teacher/tasks', [TaskController::class, 'store'])->name('teacher.tasks.store');
    Route::get('/teacher/tasks/{task}', [TaskSubmissionController::class, 'show'])->name('teacher.tasks.show');
    Route::get('/teacher/tasks/{task}/download', [TaskController::class, 'download'])->name('teacher.tasks.download');

    // Task Submission Routes
    Route::post('/teacher/tasks/{task}/submissions', [TaskSubmissionController::class, 'store'])->name('teacher.task-submissions.store');
    Route::get('/teacher/task-submissions/{submission}/download', [TaskSubmissionController::class, 'downloadSubmission'])->name('teacher.task-submissions.download');
    Route::patch('/teacher/task-submissions/{submission}/status', [TaskSubmissionController::class, 'updateStatus'])->name('teacher.task-submissions.update-status');

    // Teacher Assessment Routes
    Route::post('/teacher/tasks/{task}/assessments', [TaskAssessmentController::class, 'storeTeacher'])->name('teacher.task-assessments.store');
});

Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/student/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
    Route::get('/student/courses', [StudentController::class, 'indexCourses'])->name('student.courses.index');
    Route::get('/student/courses/{course}', [StudentController::class, 'showCourse'])->name('student.courses.show');
    Route::post('/student/tasks/{task}/submissions', [TaskSubmissionController::class, 'store'])->name('student.task-submissions.store');
    Route::post('/student/tasks/{task}/assessments', [TaskAssessmentController::class, 'store'])->name('student.task-assessments.store');
    Route::get('/student/tasks/{task}/download', [TaskController::class, 'download'])->name('student.tasks.download');
    Route::get('/student/task-submissions/{submission}/download', [TaskSubmissionController::class, 'downloadSubmission'])->name('student.task-submissions.download');
});

require __DIR__.'/auth.php';
