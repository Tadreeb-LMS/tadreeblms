<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

//use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
//use Spatie\MediaLibrary\HasMedia\HasMedia;
use Illuminate\Support\Facades\File;
// use Mtownsend\ReadTime\ReadTime;


class Lesson extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'temp_id',
        'title', 
        //'arabic_title', 
        'slug', 'lesson_image', 'short_text', 'full_text', 'position', 'downloadable_files', 'free_lesson', 'published', 'course_id'];

    protected $appends = ['image','lesson_readtime'];


    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {

        static::deleting(function ($lesson) { // before delete() method call this
            if ($lesson->isForceDeleting()) {
                $media = $lesson->media;
                foreach ($media as $item) {
                    if (File::exists(public_path('/storage/uploads/' . $item->name))) {
                        File::delete(public_path('/storage/uploads/' . $item->name));
                    }
                }
                $lesson->media()->delete();
            }

        });
    }


    /**
     * Set to null if empty
     * @param $input
     */
    public function setCourseIdAttribute($input)
    {
        $this->attributes['course_id'] = $input ? $input : null;
    }

    public function getImageAttribute()
    {
        if ($this->attributes['lesson_image'] != NULL) {
            return url('storage/uploads/'.$this->lesson_image);
        }
        return NULL;
    }


    public function videos()
{
    return $this->hasMany(LessonVideo::class)->orderBy('sort_order');
}

    public function getLessonReadtimeAttribute(){
        if($this->full_text != null){
            $text = strip_tags($this->full_text);
            $wordCount = str_word_count($text);
            $minutes = ceil($wordCount / 200);
            return $minutes;
        }
        return 0;
    }

    public function lessonMediaAttribute(){

    }


    /**
     * Set attribute to money format
     * @param $input
     */
    public function setPositionAttribute($input)
    {
        $this->attributes['position'] = $input ? $input : null;
    }


    public function readTime()
    {
        if($this->full_text != null){
            $text = strip_tags($this->full_text);
            $wordCount = str_word_count($text);
            $minutes = ceil($wordCount / 200);
            return $minutes;
        }
        return 0;
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function test()
    {
        return $this->hasOne('App\Models\Test');
    }

    public function students()
    {
        return $this->belongsToMany('App\Models\Auth\User', 'lesson_student')->withTimestamps();
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'model');
    }

    public function latestMedia()
    {
        return $this->morphOne(Media::class, 'model')->latestOfMany();
    }

    public function chapterStudents()
    {
        return $this->morphMany(ChapterStudent::class, 'model');
    }

    public function downloadableMedia()
    {
        $types = ['youtube', 'vimeo', 'upload', 'embed', 'lesson_pdf', 'lesson_audio'];

        return $this->morphMany(Media::class, 'model')
            ->whereNotIn('type', $types);
    }


    public function mediaVideo()
    {
        $types = ['youtube', 'vimeo', 'upload', 'embed'];
        return $this->morphOne(Media::class, 'model')
            ->whereIn('type', $types);

    }

    public function mediaPDF()
    {
        return $this->morphOne(Media::class, 'model')
            ->where('type', '=', 'lesson_pdf');
    }

    public function mediaAudio()
    {
        return $this->morphOne(Media::class, 'model')
            ->where('type', '=', 'lesson_audio');
    }

    public function courseTimeline()
    {
        return $this->morphOne(CourseTimeline::class, 'model');
    }

    public function isCompleted()
    {
        $isCompleted = $this->chapterStudents()->where('user_id', \Auth::id())->count();
        if ($isCompleted > 0) {
            return true;
        }
        return false;

    }

    public function scopeOfTeacher($query)
    {
        if (!auth()->user()->isAdmin()) {
            return $query->whereHas('course.teachers', function ($q) {
                $q->where('course_user.user_id', '=', auth()->user()->id);
            });
        }
        return $query;
    }

    public function liveLessonSlots()
    {
        return $this->hasMany(LiveLessonSlot::class);
    }

    public function attendance_list()
    {
        return $this->hasMany(AttendanceStudent::class,'lesson_id');
    }

    public function lessonSlotBooking()
    {
        return $this->hasOne(LessonSlotBooking::class);
    }

}
