<?php

namespace Tests\Unit\Frontend;

use Tests\TestCase;
use App\Models\Auth\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\Frontend\Auth\UserRepository;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->app->make(UserRepository::class);
    }

    /** @test */
    public function it_uploads_an_avatar_when_optional_profile_fields_are_missing()
    {
        Storage::fake('public');
        config(['access.users.change_email' => true]);

        $user = factory(User::class)->create([
            'arabic_first_name' => 'Original Arabic First',
            'arabic_last_name' => 'Original Arabic Last',
            'fav_lang' => 'english',
        ]);

        $this->actingAs($user);

        $this->userRepository->update($user->id, [
            'first_name' => 'Updated',
            'last_name' => 'User',
            'avatar_type' => 'storage',
        ], UploadedFile::fake()->image('avatar.jpg'));

        $user = $user->fresh();

        $this->assertEquals('Updated', $user->first_name);
        $this->assertEquals('User', $user->last_name);
        $this->assertEquals('Original Arabic First', $user->arabic_first_name);
        $this->assertEquals('Original Arabic Last', $user->arabic_last_name);
        $this->assertEquals('english', $user->fav_lang);
        $this->assertEquals('storage', $user->avatar_type);
        Storage::disk('public')->assertExists($user->avatar_location);
    }

    /** @test */
    public function it_preserves_profile_fields_that_are_not_submitted()
    {
        $user = factory(User::class)->create([
            'arabic_first_name' => 'Original Arabic First',
            'arabic_last_name' => 'Original Arabic Last',
            'fav_lang' => 'arabic',
            'dob' => '1990-01-01',
            'phone' => '1234567890',
            'gender' => 'male',
            'address' => 'Original Address',
            'city' => 'Original City',
            'pincode' => '12345',
            'state' => 'Original State',
            'country' => 'Original Country',
        ]);

        $this->actingAs($user);

        $this->userRepository->update($user->id, [
            'first_name' => 'Updated',
            'last_name' => 'User',
            'avatar_type' => 'gravatar',
        ]);

        $user = $user->fresh();

        $this->assertEquals('Updated', $user->first_name);
        $this->assertEquals('User', $user->last_name);
        $this->assertEquals('Original Arabic First', $user->arabic_first_name);
        $this->assertEquals('Original Arabic Last', $user->arabic_last_name);
        $this->assertEquals('arabic', $user->fav_lang);
        $this->assertEquals('1990-01-01', $user->dob);
        $this->assertEquals('1234567890', $user->phone);
        $this->assertEquals('male', $user->gender);
        $this->assertEquals('Original Address', $user->address);
        $this->assertEquals('Original City', $user->city);
        $this->assertEquals('12345', $user->pincode);
        $this->assertEquals('Original State', $user->state);
        $this->assertEquals('Original Country', $user->country);
    }
}
