<?php

namespace Tests\Unit;

use App\Helpers\CaptchaGenerator;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CaptchaGeneratorTest extends TestCase
{
    /** @test */
    public function it_generates_captcha_with_code_and_image()
    {
        $result = CaptchaGenerator::generate();

        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('image', $result);
        $this->assertEquals(6, strlen($result['code']));
        $this->assertStringStartsWith('data:image/png;base64,', $result['image']);
    }

    /** @test */
    public function it_stores_captcha_answer_in_session()
    {
        CaptchaGenerator::generate();

        $this->assertTrue(Session::has('captcha_answer'));
        $this->assertEquals(6, strlen(Session::get('captcha_answer')));
    }

    /** @test */
    public function it_generates_image_with_sufficient_dimensions()
    {
        $result = CaptchaGenerator::generate();

        // Decode the base64 image to check dimensions
        $base64 = str_replace('data:image/png;base64,', '', $result['image']);
        $imageData = base64_decode($base64);
        $image = imagecreatefromstring($imageData);

        $width = imagesx($image);
        $height = imagesy($image);

        // After fix: image should be at least 300x90 (200x60 * 1.5x)
        $this->assertGreaterThanOrEqual(300, $width, 'CAPTCHA image width should be at least 300px');
        $this->assertGreaterThanOrEqual(90, $height, 'CAPTCHA image height should be at least 90px');

        imagedestroy($image);
    }

    /** @test */
    public function it_validates_correct_captcha_input()
    {
        $result = CaptchaGenerator::generate();
        $code = $result['code'];

        $this->assertTrue(CaptchaGenerator::validate($code));
    }

    /** @test */
    public function it_validates_captcha_case_insensitively()
    {
        $result = CaptchaGenerator::generate();
        $code = strtoupper($result['code']);

        $this->assertTrue(CaptchaGenerator::validate($code));
    }

    /** @test */
    public function it_rejects_wrong_captcha_input()
    {
        CaptchaGenerator::generate();

        $this->assertFalse(CaptchaGenerator::validate('wrongcode'));
    }

    /** @test */
    public function it_rejects_empty_captcha_input()
    {
        CaptchaGenerator::generate();

        $this->assertFalse(CaptchaGenerator::validate(''));
        $this->assertFalse(CaptchaGenerator::validate(null));
    }

    /** @test */
    public function it_generates_different_codes_each_time()
    {
        $result1 = CaptchaGenerator::generate();
        $result2 = CaptchaGenerator::generate();

        // While technically possible to get the same code twice,
        // with 62^6 combinations this is astronomically unlikely
        $this->assertNotEquals($result1['code'], $result2['code']);
    }
}
