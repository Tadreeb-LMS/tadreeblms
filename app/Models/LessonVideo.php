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
}