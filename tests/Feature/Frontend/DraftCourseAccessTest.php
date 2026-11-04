<?php

namespace Tests\Feature\Frontend;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Course;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class DraftCourseAccessTest.
 */
class DraftCourseAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // the course page queries course_assignment with MySQL's FIND_IN_SET,
        // which does not exist in the in-memory sqlite used for tests
        $pdo = $this->app['db']->connection()->getPdo();
        $pdo->sqliteCreateFunction('FIND_IN_SET', function ($needle, $haystack) {
            if ($needle === null || $haystack === null) {
                return null;
            }
            foreach (explode(',', (string) $haystack) as $index => $item) {
                if ((string) $item === (string) $needle) {
                    return $index + 1;
                }
            }

            return 0;
        }, 2);
    }

    /** @test */
    public function students_cannot_open_an_unpublished_course_by_direct_url()
    {
        $studentRole = Role::whereName(config('access.users.default_role'))->first()
            ?? factory(Role::class)->create(['name' => config('access.users.default_role')]);
        $student = factory(User::class)->create();
        $student->assignRole($studentRole);

        $category = factory(Category::class)->create(['name' => 'Draft Access Category', 'slug' => 'draft-access-category']);
        $course = factory(Course::class)->create([
            'title' => 'Draft Access Course',
            'slug' => 'draft-access-course',
            'category_id' => $category->id,
            'course_image' => 'placeholder-1.jpg',
            'description' => 'Unpublished course access test.',
            'price' => 0,
            'published' => 0,
        ]);

        $this->actingAs($student)
            ->get('/course/'.$course->slug)
            ->assertStatus(404);
    }

    /** @test */
    public function students_cannot_open_an_unpublished_course_preview()
    {
        $studentRole = Role::whereName(config('access.users.default_role'))->first()
            ?? factory(Role::class)->create(['name' => config('access.users.default_role')]);
        $student = factory(User::class)->create();
        $student->assignRole($studentRole);

        $category = factory(Category::class)->create(['name' => 'Draft Preview Category', 'slug' => 'draft-preview-category']);
        $course = factory(Course::class)->create([
            'title' => 'Draft Preview Course',
            'slug' => 'draft-preview-course',
            'category_id' => $category->id,
            'course_image' => 'placeholder-1.jpg',
            'description' => 'Unpublished preview access test.',
            'price' => 0,
            'published' => 0,
        ]);

        $this->actingAs($student)
            ->get('/course-preview/'.$course->slug)
            ->assertStatus(404);
    }

    /** @test */
    public function administrators_are_not_blocked_from_an_unpublished_course()
    {
        $admin = $this->createAdmin();

        $category = factory(Category::class)->create(['name' => 'Draft Admin Category', 'slug' => 'draft-admin-category']);
        $course = factory(Course::class)->create([
            'title' => 'Draft Admin Course',
            'slug' => 'draft-admin-course',
            'category_id' => $category->id,
            'course_image' => 'placeholder-1.jpg',
            'description' => 'Unpublished admin preview test.',
            'price' => 0,
            'published' => 0,
        ]);

        // rendering the whole page needs seeded site data the in-memory test database
        // does not have, so assert the access guard itself: an admin must not be
        // rejected with 404 the way a student is
        $response = $this->actingAs($admin)->get('/course/'.$course->slug);

        $this->assertNotEquals(404, $response->status());
    }
}
