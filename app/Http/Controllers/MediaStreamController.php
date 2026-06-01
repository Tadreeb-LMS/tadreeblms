<?php

namespace App\Http\Controllers;

use App\Models\LessonVideo;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MediaStreamController extends Controller
{
    public function media(Request $request, Media $media)
    {
        if ($media->type !== 'upload') {
            abort(404);
        }

        return $this->streamLocalFile($request, $this->mediaPath($media));
    }

    public function lessonVideo(Request $request, LessonVideo $lessonVideo)
    {
        if ($lessonVideo->type !== 'upload') {
            abort(404);
        }

        return $this->streamLocalFile($request, $this->lessonVideoPath($lessonVideo));
    }

    private function mediaPath(Media $media)
    {
        $fileName = $media->file_name;

        if (!$fileName && !empty($media->getRawOriginal('url'))) {
            $fileName = basename(parse_url($media->getRawOriginal('url'), PHP_URL_PATH));
        }

        if (!$fileName) {
            abort(404);
        }

        return public_path('storage/uploads/' . basename($fileName));
    }

    private function lessonVideoPath(LessonVideo $lessonVideo)
    {
        if ($lessonVideo->file_path) {
            return storage_path('app/public/' . ltrim($lessonVideo->file_path, '/'));
        }

        if (!$lessonVideo->url) {
            abort(404);
        }

        $urlPath = parse_url($lessonVideo->url, PHP_URL_PATH);
        if (!$urlPath || strpos($urlPath, '/storage/') === false) {
            abort(404);
        }

        return public_path(ltrim($urlPath, '/'));
    }

    private function streamLocalFile(Request $request, $path)
    {
        if (!File::exists($path) || !File::isFile($path)) {
            abort(404);
        }

        $size = File::size($path);
        $start = 0;
        $end = $size - 1;
        $status = 200;

        $range = $request->headers->get('Range');
        if ($range && preg_match('/bytes=(\d*)-(\d*)/', $range, $matches)) {
            if ($matches[1] !== '') {
                $start = (int) $matches[1];
            }

            if ($matches[2] !== '') {
                $end = min((int) $matches[2], $end);
            }

            if ($matches[1] === '' && $matches[2] !== '') {
                $suffixLength = (int) $matches[2];
                $start = max($size - $suffixLength, 0);
            }

            if ($start > $end || $start >= $size) {
                return response('', 416, [
                    'Accept-Ranges' => 'bytes',
                    'Content-Range' => 'bytes */' . $size,
                ]);
            }

            $status = 206;
        }

        $length = $end - $start + 1;
        $headers = [
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'private, max-age=3600',
            'Content-Length' => $length,
            'Content-Type' => File::mimeType($path) ?: 'video/mp4',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($status === 206) {
            $headers['Content-Range'] = 'bytes ' . $start . '-' . $end . '/' . $size;
        }

        return response()->stream(function () use ($path, $start, $length) {
            $handle = fopen($path, 'rb');
            fseek($handle, $start);

            $remaining = $length;
            while ($remaining > 0 && !feof($handle)) {
                $chunkSize = min(8192, $remaining);
                echo fread($handle, $chunkSize);
                flush();
                $remaining -= $chunkSize;
            }

            fclose($handle);
        }, $status, $headers);
    }
}
