<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\LearningGroup;
use App\Models\Subjects;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskSubmissionGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_leader_student_cannot_submit_task(): void
    {
        $teacher = $this->createUser('teacher');
        $student = $this->createUser('student');

        $subject = Subjects::create([
            'user_id' => $teacher->id,
            'subject_name' => 'General',
        ]);

        $course = Course::create([
            'title' => 'Math Class',
            'description' => 'Algebra basics',
            'teacher_id' => $teacher->id,
        ]);

        $group = LearningGroup::create([
            'name' => 'Group A',
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'course_id' => $course->id,
        ]);

        $group->members()->attach($student->id, ['role' => 'member']);

        $task = Task::create([
            'course_id' => $course->id,
            'label' => 'Task 1',
            'title' => 'Task 1',
            'description' => 'Submit worksheet',
            'start_date' => now()->subHour(),
            'deadline' => now()->addDay(),
        ]);

        $response = $this
            ->actingAs($student)
            ->from('/student/dashboard')
            ->post(route('student.task-submissions.store', $task->id), [
                'description' => 'My answer',
            ]);

        $response
            ->assertRedirect('/student/dashboard')
            ->assertSessionHas('error', 'Only team leader can submit task.');

        $this->assertDatabaseCount('task_submissions', 0);
    }

    public function test_leader_cannot_submit_after_deadline(): void
    {
        $teacher = $this->createUser('teacher');
        $leader = $this->createUser('student');

        $subject = Subjects::create([
            'user_id' => $teacher->id,
            'subject_name' => 'General',
        ]);

        $course = Course::create([
            'title' => 'Science Class',
            'description' => 'Physics basics',
            'teacher_id' => $teacher->id,
        ]);

        $group = LearningGroup::create([
            'name' => 'Group B',
            'subject_id' => $subject->id,
            'created_by' => $teacher->id,
            'course_id' => $course->id,
        ]);

        $group->members()->attach($leader->id, ['role' => 'leader']);

        $task = Task::create([
            'course_id' => $course->id,
            'label' => 'Task 1',
            'title' => 'Task 2',
            'description' => 'Submit report',
            'start_date' => now()->subDays(2),
            'deadline' => now()->subMinute(),
        ]);

        $response = $this
            ->actingAs($leader)
            ->from('/student/dashboard')
            ->post(route('student.task-submissions.store', $task->id), [
                'description' => 'Late answer',
            ]);

        $response
            ->assertRedirect('/student/dashboard')
            ->assertSessionHas('error', 'Deadline has passed. You can no longer upload this task.');

        $this->assertDatabaseCount('task_submissions', 0);
    }

    private function createUser(string $role): User
    {
        return User::factory()->create([
            'username' => fake()->unique()->numerify('08##########'),
            'role' => $role,
        ]);
    }
}
