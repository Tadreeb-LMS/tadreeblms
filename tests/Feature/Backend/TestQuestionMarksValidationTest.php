<?php

namespace Tests\Feature\Backend;

use App\Http\Controllers\Backend\Admin\TestQuestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class TestQuestionMarksValidationTest extends TestCase
{
    public function test_store_rejects_marks_above_100(): void
    {
        $response = app(TestQuestionController::class)->store(Request::create('/admin/test_questions', 'POST', [
            'marks' => 150,
            'question_type' => 3,
        ]));

        $payload = json_decode($response->getContent(), true);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertFalse($payload['success']);
        $this->assertStringContainsString('100', $payload['message']);
    }

    public function test_store_accepts_marks_at_the_100_boundary(): void
    {
        $courseId = $this->createCourse('Boundary marks course');

        $response = app(TestQuestionController::class)->store(Request::create('/admin/test_questions', 'POST', [
            'marks' => 100,
            'question_type' => 3,
            'solution' => 'Sample solution',
            'course_id' => $courseId,
            'question' => 'Sample question',
        ]));

        $payload = json_decode($response, true);

        $this->assertEquals(200, $payload['code']);
        $this->assertDatabaseHas('test_questions', [
            'question_text' => 'Sample question',
            'marks' => 100,
        ]);
    }

    public function test_update_rejects_marks_above_100(): void
    {
        $questionId = $this->seedQuestion();

        $response = app(TestQuestionController::class)->update(Request::create('/admin/test_questions/update', 'POST', [
            'id' => $questionId,
            'marks' => 101,
            'question_type' => 3,
            'solution' => 'Updated solution',
        ]));

        $payload = json_decode($response->getContent(), true);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertFalse($payload['success']);
        $this->assertStringContainsString('100', $payload['message']);
    }

    public function test_update_accepts_marks_at_the_100_boundary(): void
    {
        $questionId = $this->seedQuestion();

        $response = app(TestQuestionController::class)->update(Request::create('/admin/test_questions/update', 'POST', [
            'id' => $questionId,
            'marks' => 100,
            'question_type' => 3,
            'solution' => 'Updated solution',
        ]));

        $payload = json_decode($response->getContent(), true);

        $this->assertEquals(200, $payload['code']);
        $this->assertDatabaseHas('test_questions', [
            'id' => $questionId,
            'marks' => 100,
        ]);
    }

    private function seedQuestion(): int
    {
        $courseId = $this->createCourse('Marks validation course');
        $testId = DB::table('tests')->insertGetId([
            'course_id' => $courseId,
            'title' => 'Marks validation test',
            'published' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('test_questions')->insertGetId([
            'test_id' => $testId,
            'question_type' => 3,
            'question_text' => 'Original question',
            'solution' => 'Original solution',
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
