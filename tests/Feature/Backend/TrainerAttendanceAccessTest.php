<?php

namespace Tests\Feature\Backend;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\AttendanceStudent;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class TrainerAttendanceAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // AppServiceProvider shares these with every view once the locales
        // table exists; the provider boots before RefreshDatabase migrates.
        View::share([
            'locale_full_name' => 'English',
            'locales' => [],
        ]);
    }

    /**
     * Create a user carrying the given role, mirroring the seeded roles.
     */
    private function createUserWithRole(string $roleName): User
    {
        $role = Role::where('name', $roleName)->first()
            ?: factory(Role::class)->create(['name' => $roleName]);

        $user = factory(User::class)->create();
        $user->assignRole($role);

        return $user;
    }

    /**
     * Build a course owned by the given trainer with one lesson and one attendance row.
     *
     * @return array{0: Course, 1: Lesson}
     */
    private function createCourseWithLesson(User $teacher): array
    {
        $category = Category::create([
            'name' => 'Trainer Attendance Category',
            'slug' => 'trainer-attendance-category',
        ]);

        $course = Course::create([
            'title' => 'Trainer Attendance Course',
            'slug' => 'trainer-attendance-course',
            'category_id' => $category->id,
            'description' => 'Course used by the trainer attendance access test.',
            'price' => 0,
            'published' => 1,
        ]);

        $course->teachers()->attach($teacher->id);

        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Trainer Attendance Lesson',
            'slug' => 'trainer-attendance-lesson',
            'position' => 1,
            'published' => 1,
        ]);

        AttendanceStudent::create([
            'student_id' => factory(User::class)->create()->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
        ]);

        return [$course, $lesson];
    }

    public function test_trainer_can_open_the_attendance_list_of_their_own_lesson(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        [$course, $lesson] = $this->createCourseWithLesson($teacher);

        $response = $this->actingAs($teacher)
            ->get(route('attendance.attendance.list', [$course->id, $lesson->id]));

        $response->assertOk();
        $response->assertSee('Attendance List');
        $response->assertSee('id="myTable"', false);
    }

    public function test_trainer_can_load_the_attendance_data_of_their_own_lesson(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        [$course, $lesson] = $this->createCourseWithLesson($teacher);

        $this->actingAs($teacher)
            ->getJson(route('attendance.attendance.get_data', [$course->id, $lesson->id]))
            ->assertOk();
    }

    public function test_trainer_cannot_open_the_attendance_list_of_another_trainers_lesson(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        $otherTeacher = $this->createUserWithRole('teacher');
        [$course, $lesson] = $this->createCourseWithLesson($otherTeacher);

        $this->actingAs($teacher)
            ->get(route('attendance.attendance.list', [$course->id, $lesson->id]))
            ->assertForbidden();
    }

    public function test_trainer_cannot_load_the_attendance_data_of_another_trainers_lesson(): void
    {
        $teacher = $this->createUserWithRole('teacher');
        $otherTeacher = $this->createUserWithRole('teacher');
        [$course, $lesson] = $this->createCourseWithLesson($otherTeacher);

        $this->actingAs($teacher)
            ->getJson(route('attendance.attendance.get_data', [$course->id, $lesson->id]))
            ->assertForbidden();
    }

    public function test_administrator_can_still_open_the_attendance_list(): void
    {
        $admin = $this->createAdmin();
        $teacher = $this->createUserWithRole('teacher');
        [$course, $lesson] = $this->createCourseWithLesson($teacher);

        $this->actingAs($admin)
            ->get(route('attendance.attendance.list', [$course->id, $lesson->id]))
            ->assertOk();
    }
}
