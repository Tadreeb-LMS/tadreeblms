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
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Billable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use App\Models\Auth\Traits\Scope\UserScope;
use App\Models\Auth\Traits\Method\UserMethod;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\Traits\SendUserPasswordReset;
use App\Models\Auth\Traits\Attribute\UserAttribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Auth\Traits\Relationship\UserRelationship;
use App\Models\Earning;
use App\Models\TeacherProfile;
use App\Models\Withdraw;
use Lexx\ChatMessenger\Traits\Messagable;
use Tymon\JWTAuth\Contracts\JWTSubject;



/**
 * Class User.
 */
// class User extends Authenticatable
class User extends Authenticatable
{
    use Billable {
        invoices as StripeInvoices;
    }
    use  HasRoles,
        Notifiable,
        SendUserPasswordReset,
        SoftDeletes,
        UserAttribute,
        UserMethod,
        UserRelationship,
        UserScope,
        Uuid;
    use HasApiTokens;
    // use Messagable {
    //     UserAttribute::getNameAttribute insteadof Messagable;
    // }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
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
        'fav_lang'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * @var array
     */
    protected $dates = ['last_login_at', 'deleted_at'];

    /**
     * The dynamic attributes from mutators that should be returned with the user object.
     * @var array
     */
    protected $appends = ['full_name', 'image'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'confirmed' => 'boolean',
    ];

    public function scopeActive($query)
    {
        $query->where('active', 1);
    }

    public function scopeStudent($query)
    {
        $query->whereHas('roles', function ($q) {
            $q->where('role_id', 3)->where('employee_type', 'external')->orWhere('employee_type', 'internal');
        })->active();
    }


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


    public function getImageAttribute()
    {
        return $this->picture;
    }

    public function employee()
    {
        return $this->belongsTo(EmployeeProfile::class, 'id', 'user_id');
    }

    public function assessment()
    {
        return $this->belongsTo(AssessmentProfile::class, 'id', 'user_id');
    }


    //Calc Watch Time
    public function getWatchTime()
    {
        $watch_time = VideoProgress::where('user_id', '=', $this->id)->sum('progress');
        return $watch_time;
    }

    //Check Participation Percentage
    public function getParticipationPercentage()
    {
        $videos = Media::featured()->where('status', '!=', 0)->get();
        $count = $videos->count();
        $total_percentage = 0;
        if ($count > 0) {
            foreach ($videos as $video) {
                $total_percentage = $total_percentage + $video->getProgressPercentage($this->id);
            }
            $percentage = $total_percentage / $count;
        } else {
            $percentage = 0;
        }
        return round($percentage, 2);
    }

    //Get Certificates
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }



    public function pendingOrders()
    {
        $orders = Order::where('status', '=', 0)
            ->where('user_id', '=', $this->id)
            ->get();

        return $orders;
    }

    public function purchasedCourses()
    {
        $orders = Order::where('status', '=', 1)
            ->where('order_type', '=', 0)
            ->where('user_id', '=', $this->id)
            ->pluck('id');
        $courses_id = OrderItem::whereIn('order_id', $orders)
            ->where('item_type', '=', "App\Models\Course")
            ->pluck('item_id');
        $courses = Course::whereIn('id', $courses_id)
            ->get();
        return $courses;
    }

    public function purchasedBundles()
    {
        $orders = Order::where('status', '=', 1)
            ->where('order_type', '=', 0)
            ->where('user_id', '=', $this->id)
            ->pluck('id');
        $bundles_id = OrderItem::whereIn('order_id', $orders)
            ->where('item_type', '=', "App\Models\Bundle")
            ->pluck('item_id');
        $bundles = Bundle::whereIn('id', $bundles_id)
            ->get();

        return $bundles;
    }


    public function purchases()
    {
        $orders = Order::where('status', '=', 1)
            ->where('user_id', '=', $this->id)
            ->pluck('id');
        $courses_id = OrderItem::whereIn('order_id', $orders)
            ->pluck('item_id');
        $purchases = Course::where('published', '=', 1)
            ->whereIn('id', $courses_id)
            ->get();
        return $purchases;
    }

    public function findForPassport($user)
    {
        $user = $this->where('email', $user)->first();
        if ($user->hasRole('student')) {
            return $user;
        }
    }

    /**
     * Get the teacher profile that owns the user.
     */
    public function teacherProfile()
    {
        return $this->hasOne(TeacherProfile::class);
    }

    /**
     * Get the earning owns the teacher.
     */
    public function earnings()
    {
        return $this->hasMany(Earning::class, 'user_id', 'id');
    }

    /**
     * Get the withdraw owns the teacher.
     */
    public function withdraws()
    {
        return $this->hasMany(Withdraw::class, 'user_id', 'id');
    }

    public function threads()
    {
        return $this->belongsToMany(
            config('chatmessenger.thread_model'),
            'chat_participants',
            'user_id',
            'thread_id'
        )->withPivot('last_read');
    }

    public function lessonSlotBookings()
    {
        return $this->hasMany(LessonSlotBooking::class);
    }

    public function order()
    {
        return $this->hasMany(Order::class);
    }

    public function subscribedCourse()
    {
        $orders = Order::where('order_type', '=', 1)
            ->where('user_id', '=', $this->id)
            ->pluck('id');
        $courses_id = OrderItem::whereIn('order_id', $orders)
            ->where('item_type', '=', Course::class)
            ->pluck('item_id');
        return Course::whereHas('courseUser', function ($q) {
            $q->whereDate('expire_at', '>=', Carbon::now());
        })->whereIn('id', $courses_id)->get();
    }

    public function subscribedBundles()
    {
        $orders = Order::where('order_type', '=', 1)
            ->where('user_id', '=', $this->id)
            ->pluck('id');
        $bundles_id = OrderItem::whereIn('order_id', $orders)
            ->where('item_type', '=', Bundle::class)
            ->pluck('item_id');

        return Bundle::whereHas('bundleUser', function ($q) {
            $q->whereDate('expire_at', '>=', Carbon::now());
        })->whereIn('id', $bundles_id)->get();
    }

    public function getSubscribedCoursesIds()
    {
        $courseIds = $this->subscribedCourse()->pluck('id')->toArray();
        if ($this->subscribedBundles()->count()) {
            foreach ($this->subscribedBundles() as $bundle) {
                $courseIds = array_merge($courseIds, $bundle->courses()->pluck('id')->toArray());
            }
        }
        return $courseIds;
    }

    public function getPurchasedCoursesIds()
    {
        $courseIds = $this->purchasedCourses()->pluck('id')->toArray();
        if ($this->purchasedBundles()->count()) {
            foreach ($this->purchasedBundles() as $bundle) {
                $courseIds = array_merge($courseIds, $bundle->courses()->pluck('id')->toArray());
            }
        }
        return $courseIds;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wishlist()
    {
        return $this->hasMany(WishList::class);
    }

    public function checkPlanSubcribeUser()
    {
        $subscriptionCheck = Subscription::where('user_id', '=', $this->id)->get();
        $plansArr = [];
        if (!empty($subscriptionCheck)) {
            foreach ($subscriptionCheck as $subscribe) {
                $plans = StripePlan::where('plan_id', '=', $subscribe->stripe_plan)->select('id')->get();
                if (!empty($plans)) {
                    $plansArr[] = $plans;
                }
            }
        }
        return $plansArr;
    }


    public function subscribeCourses($user_id, $courseStatus = null, $byDueDate = null, $courseName = null)
    {

        $course_status = null;
        if($courseStatus == 'Completed') {
            $course_status = 2;
        }
        if($courseStatus == 'InProgress') {
            $course_status = 1;
        }
        if($courseStatus == 'NotStarted') {
            $course_status = 0;
        }


        //dd($course_status);

        // $by_due_date = null;
        // if($byDueDate == 'soon') {
        //     $by_due_date = 'soon';
        // }
        // if($byDueDate == 'late') {
        //     $by_due_date = 'late';
        // }
        

        $subscriptionList = SubscribeCourse::notPathway()
            ->with(['course','course.lessons','course.publishedLessons','course.publishedCourseLessons'])
            ->where('user_id', '=', $user_id)
            //->where('course_id', '=', '300')
            ->when(!empty($courseName), function ($q) use ($courseName) {
                $q->whereHas('course', function ($query) use ($courseName) {
                    $query->where('title', 'LIKE', "%{$courseName}%");
                    $query->orWhere('arabic_title', 'LIKE', "%{$courseName}%");
                });
            })
            ->when(!empty($courseStatus), function ($q) use ($course_status) {

                if ($course_status == 2) {
                    $q->where('grant_certificate', '1');
                    $q->where('assignment_progress', '100');
                } elseif ($course_status == 1) {
                    $q->whereBetween('assignment_progress', [1, 99]); // strictly in-progress
                } else {
                    $q->where(function ($query) {
                        $query->where('assignment_progress', 0)
                            ->orWhereNull('assignment_progress');
                    });
                }
                
            })
            ->when(!empty($byDueDate), function ($q) use ($byDueDate) {

                $q->when($byDueDate == 'late', function ($query) {
                    $query->where('due_date', '<', Carbon::now());
                });

                $q->when($byDueDate == 'soon', function ($query) {
                    $query->whereBetween('due_date', [Carbon::now(), Carbon::now()->addDays(7)]);
                });

            })
            ->whereHas('course')
            ->orderBy('is_completed', 'asc')
            ->groupBy('course_id')
            ->get();

        //dd($subscriptionList);

        if (!empty($subscriptionList)) {
            return $subscriptionList;
        }
        return [];
    }



    public function getDepartment()
    {
        $department = DB::table('employee_profiles')
            ->select('department.title as department')
            ->leftJoin('department', 'department.id', 'employee_profiles.department')
            ->where('employee_profiles.user_id', '=', $this->id)
            //->where('employee_profiles.status','1')
            ->first();
        //dd($subscriptionList);
        if (!empty($department)) {
            return $department->department;
        }
        return '';
    }

    public function getcountry()
    {
        $country = DB::table('users')
            ->join('master_countries', 'users.nationality', '=', 'master_countries.id')
            ->select('master_countries.name as country')
            ->first();

        // $country = DB::table('users')
        //     ->select('master_countries.name as country')
        //     ->leftJoin('master_countries', 'master_countries.id', 'users.nationality')
        //     // ->where('users.id', '=', $this->id)
        //     ->first();
        if (!empty($country)) {
            return $country->country;
        }
        return '';
    }


    public function getPosition()
    {
        $position = DB::table('employee_profiles')
            ->select('employee_profiles.position as position')
            ->leftJoin('department', 'department.id', 'employee_profiles.department')
            ->where('employee_profiles.user_id', '=', $this->id)
            //->where('employee_profiles.status','1')
            ->first();
        //dd($subscriptionList);
        if (!empty($position)) {
            return $position->position;
        }
        return '';
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function bill_details()
    {
        return $this->hasMany('App\Models\UserBillDetails', 'user_id');
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    public function getArabicFullNameAttribute()
    {
        return $this->arabic_first_name . ' ' . $this->arabic_last_name;
    }

    public function unreadMessagesCount()
    {
        return 0;
    }
}
