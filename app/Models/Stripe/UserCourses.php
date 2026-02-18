<?php

namespace App\Models\Stripe;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCourses extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'plan_id','course_id','bundle_id','expire_at'];
}
