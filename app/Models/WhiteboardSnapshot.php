<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhiteboardSnapshot extends Model
{
    protected $table = 'whiteboard_snapshots';

    protected $fillable = [
        'user_id',
        'course_id',
        'image_path',
        'file_name',
        'file_size',
    ];

    /**
     * Get the user that owns this snapshot.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'user_id');
    }

    /**
     * Get the course associated with this snapshot.
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
