<?php

namespace App\Models\Auth;

use App\Models\AssessmentProfile;
use App\Models\EmployeeProfile;
use App\Models\Bundle;
use App\Models\Certificate;
use App\Models\ChapterStudent;
use App\Models\Course;
use App\Models\Invoice;
use App\Models\Lesson;
use App\Models\LessonSlotBooking;
use App\Models\Media;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Stripe\StripePlan;
use App\Models\Stripe\Subscription;
use App\Models\Stripe\SubscribeCourse;
use App\Models\Stripe\UserCourses;
use App\Models\Traits\Uuid;
use App\Models\VideoProgress;
use App\Models\WishList;
use App\Models\Earning;
use App\Models\TeacherProfile;
use App\Models\Withdraw;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Spatie\Permission\Traits\HasRoles;

/*
|--------------------------------------------------------------------------
| Custom Traits
|--------------------------------------------------------------------------
*/
use App\Models\Auth\Traits\Scope\UserScope;
use App\Models\Auth\Traits\Method\UserMethod;
use App\Models\Auth\Traits\SendUserPasswordReset;
use App\Models\Auth\Traits\Attribute\UserAttribute;
use App\Models\Auth\Traits\Relationship\UserRelationship;

/**
 * Class User
 */
class User extends Authenticatable
{
    use HasRoles,
        Notifiable,
        SendUserPasswordReset,
        SoftDeletes,
        UserAttribute,
        UserMethod,
        UserRelationship,
        UserScope,
        Uuid;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'dob',
        'phone',
        'gender',
        'address',
        'city',
        'pincode',
        'state',
        'country',
        'avatar_type',
        'avatar_location',
        'password',
        'password_changed_at',
        'active',
        'confirmation_code',
        'confirmed',
        'timezone',
        'last_login_at',
        'last_login_ip',
        'employee_type',
        'active_token',
        'arabic_last_name',
        'arabic_first_name',
        'fav_lang',
    ];

    /**
     * Hidden attributes.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Date attributes.
     */
    protected $dates = [
        'last_login_at',
        'deleted_at',
    ];

    /**
     * Appended attributes.
     */
    protected $appends = [
        'full_name',
        'image',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'active'    => 'boolean',
        'confirmed' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    public function scopeStudent($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('role_id', 3)
              ->where('employee_type', 'external')
              ->orWhere('employee_type', 'internal');
        })->active();
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_student');
    }

    public function chapters()
    {
        return $this->hasMany(ChapterStudent::class, 'user_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_user');
    }

    public function bundles()
    {
        return $this->hasMany(Bundle::class);
    }

    public function subscribed_course()
    {
        return $this->hasMany(SubscribeCourse::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function employee()
    {
        return $this->belongsTo(EmployeeProfile::class, 'id', 'user_id');
    }

    public function assessment()
    {
        return $this->belongsTo(AssessmentProfile::class, 'id', 'user_id');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function teacherProfile()
    {
        return $this->hasOne(TeacherProfile::class);
    }

    public function earnings()
    {
        return $this->hasMany(Earning::class, 'user_id');
    }

    public function withdraws()
    {
        return $this->hasMany(Withdraw::class, 'user_id');
    }

    public function lessonSlotBookings()
    {
        return $this->hasMany(LessonSlotBooking::class);
    }

    public function order()
    {
        return $this->hasMany(Order::class);
    }

    public function wishlist()
    {
        return $this->hasMany(WishList::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getImageAttribute()
    {
        return $this->picture ?? null;
    }

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getArabicFullNameAttribute()
    {
        return trim($this->arabic_first_name . ' ' . $this->arabic_last_name);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getWatchTime()
    {
        return VideoProgress::where('user_id', $this->id)->sum('progress');
    }

    public function getParticipationPercentage()
    {
        $videos = Media::featured()->where('status', '!=', 0)->get();
        $count = $videos->count();

        if ($count === 0) {
            return 0;
        }

        $total = 0;
        foreach ($videos as $video) {
            $total += $video->getProgressPercentage($this->id);
        }

        return round($total / $count, 2);
    }

    public function pendingOrders()
    {
        return Order::where('status', 0)
            ->where('user_id', $this->id)
            ->get();
    }

    public function purchasedCourses()
    {
        $orders = Order::where('status', 1)
            ->where('order_type', 0)
            ->where('user_id', $this->id)
            ->pluck('id');

        $courseIds = OrderItem::whereIn('order_id', $orders)
            ->where('item_type', Course::class)
            ->pluck('item_id');

        return Course::whereIn('id', $courseIds)->get();
    }

    public function getDepartment()
    {
        $department = DB::table('employee_profiles')
            ->select('department.title as department')
            ->leftJoin('department', 'department.id', 'employee_profiles.department')
            ->where('employee_profiles.user_id', $this->id)
            ->first();

        return $department->department ?? '';
    }
}
