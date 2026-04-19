<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeacherController;
use Illuminate\Foundation\Application;
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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
});

require __DIR__.'/auth.php';
