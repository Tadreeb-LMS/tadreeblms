<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestsResult extends Model
{

    protected $fillable = ['test_id', 'user_id', 'test_result'];

    public function answers()
    {
        return $this->hasMany('App\Models\TestsResultsAnswer');
    }

    public function lessonQuizAnswers()
    {
        return $this->hasMany('App\Models\LessonQuizAnswer', 'tests_result_id');
    }

    public function test(){
        return $this->belongsTo(Test::class);
    }
}
