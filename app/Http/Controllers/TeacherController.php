<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LearningGroup;
use App\Models\LearningGroupMembers;
use App\Models\Subjects;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TeacherController extends Controller
{
    /**
     * Show the teacher dashboard
     */
    public function dashboard(Request $request): Response
    {
        $teacher = $request->user();

        $courses = Course::where('teacher_id', $teacher->id)
            ->withCount('learningGroups')
            ->get();

        return Inertia::render('Teacher/Dashboard', [
            'courses' => $courses,
        ]);
    }

    /**
     * Show course detail with learning groups and members
     */
    public function show(Request $request, Course $course): Response
    {
        if ($course->teacher_id !== $request->user()->id) {
            abort(403);
        }

        $course->load([
            'learningGroups' => function ($query) {
                $query->with([
                    'members' => function ($memberQuery) {
                        $memberQuery->select('users.id', 'name', 'username');
                    }
                ]);
            }
        ]);

        $studentIdsInCourse = LearningGroupMembers::whereHas('learningGroup', function ($query) use ($course) {
            $query->where('course_id', $course->id);
        })->pluck('user_id');

        $availableStudents = User::where('role', 'student')
            ->whereNotIn('id', $studentIdsInCourse)
            ->get(['id', 'name', 'username']);

        return Inertia::render('Teacher/CourseDetail', [
            'course' => $course,
            'availableStudents' => $availableStudents,
        ]);
    }

    public function showStudents(Request $request): Response
    {
        $students = User::where('role', 'student')
            ->select('id', 'name', 'username', 'email', 'created_at')
            ->latest()
            ->get();

        return Inertia::render('Teacher/CourseStudents', [
            'students' => $students,
        ]);
    }

    public function storeCourse(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'teacher_id' => $request->user()->id,
        ]);

        return Redirect::route('teacher.dashboard');
    }

    public function storeLearningGroup(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'course_id' => ['required', 'exists:courses,id'],
        ]);

        $course = Course::findOrFail($request->course_id);

        if ($course->teacher_id !== $request->user()->id) {
            abort(403);
        }

        $subject = Subjects::firstOrCreate(
            ['subject_name' => 'General'],
            ['user_id' => $request->user()->id]
        );

        LearningGroup::create([
            'name' => $request->name,
            'subject_id' => $subject->id,
            'course_id' => $request->course_id,
            'created_by' => $request->user()->id,
        ]);

        return Redirect::route('teacher.courses.show', ['course' => $request->course_id]);
    }

    public function storeLearningGroupMember(Request $request, LearningGroup $learningGroup): RedirectResponse
    {
        if ($learningGroup->created_by !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'existing_student_id' => ['required', 'exists:users,id'],
        ]);

        $student = User::findOrFail($request->existing_student_id);

        $alreadyInCourse = LearningGroupMembers::where('user_id', $student->id)
            ->whereHas('learningGroup', function ($query) use ($learningGroup) {
                $query->where('course_id', $learningGroup->course_id);
            })
            ->exists();

        if ($alreadyInCourse) {
            return Redirect::back()->withErrors(['existing_student_id' => 'This student is already assigned to another group in the same course.']);
        }

        $learningGroup->members()->attach($student->id, ['role' => 'member']);

        return Redirect::route('teacher.courses.show', ['course' => $learningGroup->course_id]);
    }

    public function storeStudent(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'digits_between:10,18', Rule::unique('users', 'username')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
        ]);

        $email = $request->email ?: 'student_' . $request->username . '@noemail.local';

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $email,
            'role' => 'student',
            'password' => Hash::make('akunpelajar'),
        ]);

        return Redirect::route('teacher.students.index');
    }

    public function setLearningGroupLeader(Request $request, LearningGroup $learningGroup, User $user): RedirectResponse
    {
        if ($learningGroup->created_by !== $request->user()->id) {
            abort(403);
        }

        LearningGroupMembers::where('learning_group_id', $learningGroup->id)->update(['role' => 'member']);

        $learningGroup->members()->updateExistingPivot($user->id, ['role' => 'leader']);

        return Redirect::route('teacher.courses.show', ['course' => $learningGroup->course_id]);
    }
}
