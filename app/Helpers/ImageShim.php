<?php

namespace App\Helpers;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageShim
{
    public static function make($file)
    {
        $manager = new ImageManager(new Driver());
        
        // Handle Laravel UploadedFile
        if (is_object($file) && method_exists($file, 'getPathname')) {
            $file = $file->getPathname();
        }
        
        // Handle base64 Data URIs just in case
        if (is_string($file) && str_starts_with($file, 'data:image')) {
            // Intervention v3 supports Data URIs directly
        }

        $image = $manager->read($file);
        return new ImageShimWrapper($image);
    }
}

class ImageShimWrapper
{
    protected $image;

    public function __construct($image)
    {
        $this->image = $image;
    }

    public function resize($width, $height = null, $callback = null)
    {
        if ($callback) {
            // Constrain aspect ratio
            if ($width && !$height) {
                $this->image->scale(width: $width);
            } elseif (!$width && $height) {
                $this->image->scale(height: $height);
            } else {
                $this->image->scale(width: $width, height: $height);
            }
        } else {
            // Exact resize
            $this->image->resize($width ?? 0, $height ?? $width ?? 0);
        }
        return $this;
    }

    public function encode($format, $quality = 100)
    {
        return $this;
    }

    public function save($path, $quality = null)
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        if ($quality) {
            $this->image->save($path, quality: $quality);
        } else {
            $this->image->save($path);
        }
        return $this;
    }

    public function width()
    {
        return $this->image->width();
    }

    public function height()
    {
        return $this->image->height();
    }
}
