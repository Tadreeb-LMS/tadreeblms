<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;

class CaptchaGenerator
{
    /**
     * Generate a visual captcha with noise elements
     * 
     * @return array Returns captcha code and image data
     */
    public static function generate()
    {
        // Generate random captcha code (6 characters: letters and numbers)
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $captchaLength = 6;
        $captchaCode = '';
        
        for ($i = 0; $i < $captchaLength; $i++) {
            $captchaCode .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        // Store in session for validation (lowercase for case-insensitive comparison)
        Session::put('captcha_answer', strtolower($captchaCode));
        
        // Generate image as base64
        $imageData = self::generateCaptchaImage($captchaCode);
        
        return [
            'code' => $captchaCode,
            'image' => $imageData,
        ];
    }
    
    /**
     * Generate captcha image with noise elements
     * 
     * @param string $code The captcha code to render
     * @return string Base64 encoded image
     */
    private static function generateCaptchaImage($code)
    {
        // Create image
        $width = 150;
        $height = 50;
        $image = imagecreate($width, $height);
        
        // Background color
        $backgroundColor = imagecolorallocate($image, 240, 240, 240);
        
        // Text color
        $textColor = imagecolorallocate($image, 50, 50, 50);
        
        // Noise colors
        $noiseColors = [
            imagecolorallocate($image, 150, 150, 150),
            imagecolorallocate($image, 180, 180, 180),
            imagecolorallocate($image, 200, 200, 200),
        ];
        
        // Add noise dots
        for ($i = 0; $i < 100; $i++) {
            $noiseColor = $noiseColors[array_rand($noiseColors)];
            imagesetpixel($image, rand(0, $width), rand(0, $height), $noiseColor);
        }
        
        // Add noise lines
        for ($i = 0; $i < 5; $i++) {
            $noiseColor = $noiseColors[array_rand($noiseColors)];
            imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $noiseColor);
        }
        
        // Add captcha text
        $fontSize = 5;
        $x = ($width - strlen($code) * imagefontwidth($fontSize)) / 2;
        $y = ($height - imagefontheight($fontSize)) / 2;
        
        // Slightly randomize position for each character to make OCR harder
        for ($i = 0; $i < strlen($code); $i++) {
            $charX = $x + ($i * imagefontwidth($fontSize)) + rand(-2, 2);
            $charY = $y + rand(-2, 2);
            imagestring($image, $fontSize, $charX, $charY, $code[$i], $textColor);
        }
        
        // Add more noise lines over the text
        for ($i = 0; $i < 3; $i++) {
            $noiseColor = $noiseColors[array_rand($noiseColors)];
            imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $noiseColor);
        }
        
        // Output image as base64
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
    
    /**
     * Validate captcha input
     * 
     * @param string $input User input
     * @return bool Whether the captcha is valid
     */
    public static function validate($input)
    {
        if (empty($input)) {
            return false;
        }
        
        $storedAnswer = Session::get('captcha_answer');
        
        if (empty($storedAnswer)) {
            return false;
        }
        
        // Case-insensitive comparison with trimmed whitespace
        return strtolower(trim($input)) === strtolower(trim($storedAnswer));
    }
}