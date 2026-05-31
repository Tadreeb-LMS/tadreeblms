<?php

namespace Tests\Feature\Backend;

use App\Http\Controllers\Backend\Admin\TestQuestionController;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TestQuestionVisibilityTest extends TestCase
{
    public function test_new_trainer_without_assigned_courses_sees_no_existing_questions(): void
    {
        $this->seedQuestionForCourse('Existing course question');
        $trainer = $this->createTrainer();

        $this->actingAs($trainer->fresh());

        $questions = $this->questionIndexData();

        $this->assertCount(0, $questions);
    }

    public function test_trainer_sees_only_questions_for_assigned_courses(): void
    {
        $assignedCourseId = $this->createCourse('Assigned course');
        $unassignedCourseId = $this->createCourse('Unassigned course');
        $assignedQuestionId = $this->seedQuestionForCourse('Assigned question', $assignedCourseId);
        $this->seedQuestionForCourse('Unassigned question', $unassignedCourseId);

        $trainer = $this->createTrainer();
        DB::table('course_user')->insert([
            'course_id' => $assignedCourseId,
            'user_id' => $trainer->id,
        ]);

        $this->actingAs($trainer->fresh());

        $questions = $this->questionIndexData();

        $this->assertCount(1, $questions);
        $this->assertSame($assignedQuestionId, (int) $questions->first()->id);
    }

    public function test_admin_question_visibility_is_not_restricted_by_trainer_scope(): void
    {
        $this->seedQuestionForCourse('First admin-visible question');
        $this->seedQuestionForCourse('Second admin-visible question');

        $this->actingAs($this->createAdmin());

        $questions = $this->questionIndexData();

        $this->assertCount(2, $questions);
    }

    private function questionIndexData()
    {
        $response = app(TestQuestionController::class)->index(Request::create('/admin/test_questions', 'GET'));

        return $response->getData()['test_questions'];
    }

    private function createTrainer(): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::firstOrCreate([
            'name' => 'teacher',
            'guard_name' => 'web',
        ]);

        $trainer = factory(User::class)->create();
        $trainer->assignRole($role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $trainer;
    }

    private function seedQuestionForCourse(string $questionText, ?int $courseId = null): int
    {
        $courseId = $courseId ?: $this->createCourse($questionText . ' course');
        $testId = DB::table('tests')->insertGetId([
            'course_id' => $courseId,
            'title' => $questionText . ' test',
            'published' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('test_questions')->insertGetId([
            'test_id' => $testId,
            'question_type' => 1,
            'question_text' => $questionText,
            'marks' => 1,
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createCourse(string $title): int
    {
        $categoryId = DB::table('categories')->insertGetId([
            'name' => $title . ' category',
            'slug' => Str::slug($title . ' category'),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('courses')->insertGetId([
            'category_id' => $categoryId,
            'title' => $title,
            'slug' => Str::slug($title),
            'published' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
