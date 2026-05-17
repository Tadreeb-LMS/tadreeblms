<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonVideo extends Model
{
    protected $fillable = [
        'lesson_id',
        'title',
        'type',
        'url',
        'file_path',
        'duration',
        'sort_order',
        'is_preview'
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function getPlaybackUrlAttribute()
    {
        if ($this->type === 'upload') {
            if ($this->file_path) {
                return asset('storage/' . ltrim($this->file_path, '/'));
            }

            return $this->url;
        }

        return $this->url;
    }

    public function getPlyrEmbedIdAttribute()
    {
        $url = $this->url;
        if (!$url) {
            return null;
        }

        if ($this->type === 'youtube') {
            if (preg_match('/(?:v=|\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
                return $matches[1];
            }

            if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
                return $url;
            }
        }

        if ($this->type === 'vimeo') {
            if (preg_match('/vimeo\.com\/(?:video\/)?([0-9]+)/', $url, $matches)) {
                return $matches[1];
            }

            if (preg_match('/^[0-9]+$/', $url)) {
                return $url;
            }
        }

        return $url;
    }
}
