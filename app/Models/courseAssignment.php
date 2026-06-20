<?php

namespace App\Models;

use App\Models\Auth\User;
use App\Models\Stripe\SubscribeCourse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class courseAssignment extends Model
{
    use HasFactory;
    protected $table = 'course_assignment';
    protected $fillable = [
        'title',
        'assign_by',
        'assign_date',
        'assign_to',
        'due_date',
        'category_id',
        'department_id',
        'message',
        'course_id',
        'assign_to',
        'is_pathway',
        'learning_pathway_assignment_id',
        'assignment_id',
        'by_invitation',
        'classroom_location',
        'meeting_link',
        'meeting_end_datetime',
        'reschedule'
    ];

    public function assessment()
    {
        return $this->hasOne(Assignment::class, 'course_id', 'course_id');
        
    }

    public function assessmentDetail()
    {
        return $this->hasOne(Assignment::class, 'assignment_id');
    }


    public function subscribeCourse()
    {
        return $this->hasOne(SubscribeCourse::class, 'course_id', 'course_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function hasCourse()
    {
        return $this->hasOne(Course::class, 'id','course_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'assign_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    

    public function getAssignedUserNamesAttribute()
    {
        $names = null;
        if (strpos($this->assign_to, ',') !== false) {
            $usersAssigned = explode(',', $this->assign_to);

            foreach ($usersAssigned as $value) {
                $user = User::where('id', $value)->select('first_name', 'last_name')->first();
                if ($user) {
                    $names .= $names ? ', ' . $user->full_name : $user->full_name;
                }
            }
        } else {
            $user = User::where('id', $this->assign_to)->select('first_name', 'last_name')->first();
            if ($user) {
                $names = $user->full_name;
            }
        }

        return $names;
    }

    public function scopePathway($q)
    {
        return $q->where('is_pathway', true);
    }
    public function scopeNotPathway($q)
    {
        return $q->where('is_pathway', false);
    }
}
