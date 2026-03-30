<?php

namespace App\Http\Controllers\Backend\Admin;


use App\Exports\CoursesExport;
use App\Models\Auth\User;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\CourseTimeline;
use App\Models\Media;
use function foo\func;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCoursesRequest;
use App\Http\Requests\Admin\UpdateCoursesRequest;
use App\Http\Controllers\Traits\FileUploadTrait;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\CustomHelper;
use App\Models\{Assignment, courseAssignment, CourseAssignmentToUser, CourseFeedback, CourseModuleWeightage, Department, LiveSession};
use App\Models\Stripe\SubscribeCourse;
use App\Jobs\SendEmailJob;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use Session;
use App\Exports\CourseAssignmentReportExport;
use App\Notifications\Backend\CourseNotification;
use App\Services\NotificationSettingsService;
use Illuminate\Support\Str;

class CoursesController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of Course.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('course_access')) {
            return abort(401);
        }

        $teachers = \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('role_id', 2);
        })->get()->pluck('name', 'id');

        $categories = Category::where('status', '=', 1)->pluck('name', 'id');

        return view('backend.courses.index', compact('teachers', 'categories'));
    }
    public function cmsCourse()
    {
        if (!Gate::allows('course_access')) {
            return abort(401);
        }

        return view('backend.courses.cms-course-list');
    }

    public function getCmsData(Request $request)
    {
        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $courses = "";

        if (request('show_deleted') == 1) {
            if (!Gate::allows('course_delete')) {
                return abort(401);
            }
            $courses = Course::query()->onlyTrashed()
                // ->whereHas('category')
                ->ofTeacher()->orderBy('created_at', 'desc');
        } elseif (request('teacher_id') != "") {
            $id = request('teacher_id');
            $courses = Course::query()->ofTeacher()
                // ->whereHas('category')
                ->whereHas('teachers', function ($q) use ($id) {
                    $q->where('course_user.user_id', '=', $id);
                })->orderBy('created_at', 'desc');
        } elseif (request('cat_id') != "") {
            $id = request('cat_id');
            $courses = Course::query()->ofTeacher()
                // ->whereHas('category')
                ->where('category_id', '=', $id)->orderBy('created_at', 'desc');
        } else {
            $courses = Course::query()->ofTeacher()
                // ->whereHas('category')
                ->orderBy('created_at', 'desc');
        }

        $courses = Course::where('cms', 1)->get();


        if (auth()->user()->can('course_view')) {
            $has_view = true;
        }
        if (auth()->user()->can('course_edit')) {
            $has_edit = true;
        }
        if (auth()->user()->can('lesson_delete')) {
            $has_delete = true;
        }

        return DataTables::of($courses)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.courses', 'label' => 'id', 'value' => $q->id]);
                }
                if ($has_view) {
                    $view = view('backend.datatable.action-view')
                        ->with(['route' => route('admin.courses.show', ['course' => $q->id])])->render();
                }
                if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.courses.edit', ['course' => $q->id])])
                        ->render();
                    $view .= $edit;
                }

                if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.courses.destroy', ['course' => $q->id])])
                        ->render();
                    $view .= $delete;
                }
                if ($q->published == 1) {
                    $type = 'action-unpublish';
                } else {
                    $type = 'action-publish';
                }

                $view .= view('backend.datatable.' . $type)
                    ->with(['route' => route('admin.courses.publish', ['id' => $q->id])])->render();
                return $view;
            })
            ->addColumn('status', function ($q) {

    // Expired (only if published)
    if ($q->published == 1 && $q->expire_at && \Carbon\Carbon::parse($q->expire_at)->isPast()) {
        return "<p class='pill-expired'>Expired</p>";
    }

    // Published
    if ($q->published == 1) {
        return "<p class='pill-publish'>Published</p>";
    }

    // Unpublished (-1)
    if ($q->published == -1) {
        return "<p class='pill-unpublish'>Unpublished</p>";
    }

    // Draft (0)
    return "<p class='pill-draft'>Draft</p>";
})
            ->addColumn('teachers', function ($q) {
                $teachers = "";
                foreach ($q->teachers as $singleTeachers) {
                    $teachers .= '<span class="badge badge-info">' . $singleTeachers->name . ' </span>';
                }
                return $teachers;
            })
            ->addColumn('lessons', function ($q) {
                $lesson = '<a href="' . route('admin.lessons.create', ['course_id' => $q->id]) . '" class="btn btn-success mb-1"><i class="fa fa-plus-circle"></i></a>  <a href="' . route('admin.lessons.index', ['course_id' => $q->id]) . '" class="btn mb-1 btn-warning text-white"><i class="fa fa-arrow-circle-right"></a>';
                return $lesson;
            })
            ->addColumn('tests', function ($q) {
                $lesson = '<a href="' . route('admin.tests.create', ['course_id' => $q->id]) . '" class="btn btn-success mb-1"><i class="fa fa-plus-circle"></i></a>  <a href="' . route('admin.tests.index', ['course_id' => $q->id]) . '" class="btn mb-1 btn-warning text-white"><i class="fa fa-arrow-circle-right"></a>';
                return $lesson;
            })
            ->editColumn('course_image', function ($q) {
                return ($q->course_image != null) ? '<img height="50px" src="' . asset('storage/uploads/' . $q->course_image) . '">' : 'N/A';
            })
            ->addColumn('qr_code', function ($q) {
                return QrCode::size(80)->generate(url('register/course/' . $q->id));
            })
            
            ->editColumn('price', function ($q) {
                if ($q->free == 1) {
                    return trans('labels.backend.courses.fields.free');
                }
                return $q->price;
            })

            ->editColumn('total_students_enrolled', function ($q) {

                return '<a class="badge badge-info" href="' . route('admin.enrolled_student', ['course_id' => $q->id]) . '"> View all (' . CustomHelper::totalEnrolled($q->id) . ') </a>';
            })
            ->addColumn('department', function ($q) {
                return $q->getDepartment($q->department_id);
            })
            ->addColumn('category', function ($q) {
                return $q->category->name;
            })
              ->addColumn('price', function ($course) {
    if ($course->is_paid) {
        return '<span class="badge badge-danger">$' . $course->price . '</span>';
    } else {
        return '<span class="badge badge-success">Free</span>';
    }
})
->rawColumns(['price'])
            ->rawColumns(['teachers', 'department', 'total_students_enrolled', 'tests', 'lessons', 'course_image', 'actions', 'status'])
            ->make();
    }

    /**
     * Display a listing of Courses via ajax DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        $has_view = false;
        $has_delete = false;
        $has_edit = false;

        $courses = Course::query();

        if (request('show_deleted') == 1) {
            if (!Gate::allows('course_delete')) {
                return abort(401);
            }
            $courses = $courses->onlyTrashed();
        }

        $courses = $courses->ofTeacher();

        if (request()->filled('teacher_id')) {
            $id = request('teacher_id');
            $courses = $courses->whereHas('teachers', function ($q) use ($id) {
                $q->where('course_user.user_id', '=', $id);
            });
        }

        if (request()->filled('cat_id')) {
            $id = request('cat_id');
            $courses = $courses->where('category_id', '=', $id);
        }

        if (request()->filled('status')) {
            if (request('status') === 'published') {
                $courses = $courses->where('published', '=', 1);
            } elseif (request('status') === 'draft') {
                $courses = $courses->where('published', '=', 0);
            }
        }

        $courses = $courses->orderBy('created_at', 'desc');

        $courses = $courses->with('courseFeedback');
        //dd($courses->get());

        if (null !== (Session::get('setvaluesession'))) {
            if ((Session::get('setvaluesession')) == 2) {
                $courses = $courses->where('course_type', 2);
            } elseif ((Session::get('setvaluesession')) == 3) {
                $courses = $courses->where('course_type', 3);
            }
        }
        // DB::table('courses')->where('id',$course_id)->update(['course_type'=>$course_type]);       

        if (auth()->user()->can('course_view')) {
            $has_view = true;
        }
        if (auth()->user()->can('course_edit')) {
            $has_edit = true;
        }
        if (auth()->user()->can('lesson_delete')) {
            $has_delete = true;
        }

        return DataTables::of($courses)
            ->addIndexColumn()
           
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
        $actions = '<div class="actionbtns"> 
        <div class="dropdown">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
        
        ';

        if ($request->show_deleted == 1) {
            return view('backend.datatable.action-trashed')->with([
                'route_label' => 'admin.courses',
                'label' => 'id',
                'value' => $q->id,
            ]);
        }

    if ($has_view) {
        $actions .= view('backend.datatable.action-view')
            ->with(['route' => route('admin.courses.show', ['course' => $q->id])])
            ->render();
    }

    if ($has_edit) {
        $actions .= view('backend.datatable.action-edit')
            ->with(['route' => route('admin.courses.edit', ['course' => $q->id])])
            ->render();
    }

    if ($has_delete) {
        $actions .= view('backend.datatable.action-delete')
            ->with(['route' => route('admin.courses.destroy', ['course' => $q->id])])
            ->render();
    }

    $type = ($q->published == 1) ? 'action-unpublish' : 'action-publish';

    $actions .= view('backend.datatable.' . $type)
        ->with(['route' => route('admin.courses.publish', ['id' => $q->id])])
        ->render();

    if (!$q->is_online_course) {
        $actions .= view('backend.datatable.copy-offline-link')
            ->with(['route' => route('coursePreview', ['slug' => $q->slug])])
            ->render();
    }

    $actions .= '</div></div></div>';

    // Wrap all actions in dropdown
    return $actions;
})
            ->addColumn('teachers', function ($q) {
                $teachers = "";
                foreach ($q->teachers as $singleTeachers) {
                    if($singleTeachers->hasRole('teacher')){
                    $teachers .= '<span class="text-dark">' . $singleTeachers->name . ' </span>';
                    }
                }
                return $teachers;
            })
            // ->addColumn('lessons', function ($q) {
            //     $lesson = '<a href="' . route('admin.lessons.create', ['course_id' => $q->id]) . '" class="btn btn-success mb-1"><i class="fa fa-plus-circle"></i></a>  <a href="' . route('admin.lessons.index', ['course_id' => $q->id]) . '" class="btn mb-1 btn-warning text-white"><i class="fa fa-arrow-circle-right"></a>';
            //     return $lesson;
            // })
            ->addColumn('lessons', function ($q) {
    $dropdown = '
        
           
            <div class="">
            
                <a class="createbtn" href="' . route('admin.lessons.create', ['course_id' => $q->id]) . '">
                Create
                   <!-- <i class="fa fa-plus-circle" aria-hidden="true" style="font-size:20px"></i> -->
                </a>
                <a class="viewbtn" href="' . route('admin.lessons.index', ['course_id' => $q->id]) . '">
                View
                   <!-- <i class="fa fa-eye" aria-hidden="true" style="font-size:18px margin-bottom:-3px"></i> -->
                </a>
            </div>
        ';
    return $dropdown;
})
            // ->addColumn('tests', function ($q) {
            //     $total_tests = $q->total_tests_published()->count();

            //     $lesson = '';
            //     if($total_tests == 0) {
            //         $lesson = '<a href="' . route('admin.tests.create', ['course_id' => $q->id]) . '" class="btn btn-success mb-1"><i class="fa fa-plus-circle"></i>   </a>';
            //     } else {
            //         $lesson .=  '<a href="' . route('admin.tests.index', ['course_id' => $q->id]) . '" class="btn mb-1 btn-warning text-white"> <span class=""> '. $total_tests. '</span> <i class="fa fa-arrow-circle-right"></a>';
            //     }
                
            //     return $lesson;
            // })
            ->addColumn('tests', function ($q) {
    $total_tests = $q->total_tests_published()->count();

   if ($total_tests == 0) {
        return '<a class="btn2" href="' . route('admin.tests.create', ['course_id' => $q->id]) . '">
            <!--  <i class="fa fa-plus-square" aria-hidden="true" style="font-size:20px;margin-bottom:-3px"></i> --> 
                   Add Test
                   
                </a>';
    } else {
        return '<a class="btn2" href="' . route('admin.tests.index', ['course_id' => $q->id]) . '">
                  
                   (' . $total_tests . ') 
                   <span >View Tests</span>
                  <!-- <i class="fa fa-eye ml-1" aria-hidden="true" style="font-size:13px;margin-bottom:-1px"></i>  -->
                </a>';
    }
})

            ->editColumn('course_image', function ($q) {
                return ($q->course_image != null) ? '<img height="50px" src="' . asset('storage/uploads/' . $q->course_image) . '">' : 'N/A';
            })
            // ->addColumn('qr_code', function ($q) {
            //     return QrCode::size(80)->generate(url('register/course/' . $q->id));
            // })
          ->addColumn('qr_code', function ($q) {
    $modalId = 'qrModal_' . $q->id;
    $qrCodeHtml = \QrCode::size(200)->generate(url('register/course/' . $q->id));

    $html = '
        <a href="javascript:void(0);" data-toggle="modal" data-target="#' . $modalId . '">
            <i class="fa fa-qrcode" style="font-size:20px;color:#3c4085"></i>
        </a>

        <!-- Modal -->
        <div class="modal fade" id="' . $modalId . '" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel_' . $q->id . '" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="qrModalLabel_' . $q->id . '">QR Code</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        ' . $qrCodeHtml . '
                        <p class="mt-2 small text-muted">Scan to open course link</p>
                    </div>
                </div>
            </div>
        </div>';

    return $html;
})
            ->addColumn('status', function ($q) {

    // Expired (if published and expire date passed)
    if (
        $q->published == 1 &&
        $q->expire_at &&
        \Carbon\Carbon::parse($q->expire_at)->isPast()
    ) {
        $text = "<p class='pill-expired'>Expired</p>";
    }
    // Published
    elseif ($q->published == 1) {
        $text = "<p class='pill-publish'>Published</p>";
    }
    // Draft
    elseif ($q->published == 0) {
        $text = "<p class='pill-draft'>Draft</p>";
    }
    // Unpublished (-1)
    else {
        $text = "<p class='pill-unpublish'>Unpublished</p>";
    }

    if (auth()->user()->isAdmin()) {
        $text .= ($q->featured == 1) ? "<p class='bg-warning text-white p-1'>Featured</p>" : "";
        $text .= ($q->trending == 1) ? "<p class='bg-success text-white p-1'>Trending</p>" : "";
        $text .= ($q->popular == 1) ? "<p class='bg-primary text-white p-1'>Popular</p>" : "";
    }

    return $text;
})
            ->addColumn('start_date', function ($q) {
    if (empty($q->start_date)) {
        return '-';
    }

    return \Carbon\Carbon::parse($q->start_date)
        ->format(config('app.date_format'));
})

->addColumn('expiry_date', function ($q) {

    if (empty($q->expire_at)) {
        return '-';
    }

    $expiry = \Carbon\Carbon::parse($q->expire_at);
    $formatted = $expiry->format(config('app.date_format'));

    if ($expiry->isPast()) {
        return '<span class="badge badge-danger">'.$formatted.'</span>';
    }

    return '<span class="badge badge-success">'.$formatted.'</span>';
})
            ->editColumn('price', function ($q) {
                if ($q->free == 1) {
                    return trans('labels.backend.courses.fields.free');
                }
                return $q->price;
            })
            ->addColumn('duration', function ($q) {
                return $q->course_all_lesson_duration;
            })
            ->addColumn('assignment', function ($q) {
                //$total_tests = $q->total_tests_published()->count();
                return $q->assesmentLink;
            })
            ->addColumn('total_students_enrolled', function ($q) {

                return '<a class="viewiconbtn"  href="' . route('admin.enrolled_student', ['course_id' => $q->id]) . '"> (' . CustomHelper::totalEnrolled($q->id) . ') <br>   <i class="fa fa-eye ml-1" aria-hidden="true"></i>   </a>';
            })
            ->addColumn('department', function ($q) {
                return $q->getDepartment($q->department_id);
            })
            ->addColumn('category', function ($q) {
                return $q->category->name;
            })
            ->addColumn('feedback', function ($q) {
                if($q->courseFeedback()->count()) {
                    return 'yes';
                } else {
                    return 'no';
                }
                //return '<a class="add-btn" style="padding:7px 20px 11px 20px"  href="' . route('admin.enrolled_student', ['course_id' => $q->id]) . '"> (' . CustomHelper::totalEnrolled($q->id) . ') <i class="fa fa-eye ml-1" aria-hidden="true"></i> </a>';
            })
            ->rawColumns(['teachers', 'assignment', 'department', 'duration', 'total_students_enrolled', 'tests', 'lessons', 'course_image', 'actions', 'status','qr_code',  'expiry_date'])
            ->make();
    }


    /**
     * Show the form for creating new Course.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('course_create')) {
            return abort(401);
        }
        $teachers = \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('role_id', 2);
        })->get()->pluck('name', 'id');

        $internalStudents = \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('role_id', 3)->where('employee_type', 'internal');
        })->get()->pluck('name', 'id');

        $externalStudents = \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('role_id', 3)->where('employee_type', 'external');
        })->get()->pluck('name', 'id');

        $categories = Category::where('status', '=', 1)->pluck('name', 'id');
        $departments = Department::all();

        $enabledMeetingProviders = [];
        if (\App\Models\ExternalApp::where('slug', 'zoom')->where('is_enabled', true)->where('is_setup', true)->where('status', 'active')->exists()) {
            $enabledMeetingProviders['zoom'] = 'Zoom';
        }
        if (\App\Models\ExternalApp::where('slug', 'teams')->where('is_enabled', true)->where('is_setup', true)->where('status', 'active')->exists()) {
            $enabledMeetingProviders['teams'] = 'Microsoft Teams';
        }
        if (\App\Models\ExternalApp::where('slug', 'google-meet-integration')->where('is_enabled', true)->where('is_setup', true)->where('status', 'active')->exists()) {
            $enabledMeetingProviders['google-meet-integration'] = 'Google Meet';
        }

        return view('backend.courses.create', compact('internalStudents', 'externalStudents', 'teachers', 'categories', 'departments', 'enabledMeetingProviders'));
    }

    /**
     * Store a newly created Course in storage.
     *
     * @param  \App\Http\Requests\StoreCoursesRequest $request
     * @return \Illuminate\Http\Response
     */

    public function store(StoreCoursesRequest $request)
    {
        //dd($request->all());
        $media = '';
        if (!Gate::allows('course_create')) {
            return abort(401);
        }
        $request->validate([
             'start_date' => 'required|date',
             'expire_at'  => 'required|date|after_or_equal:start_date',
             'title' => 'required|string|max:255',
             'category_id' => 'required',
             'course_type' => 'required',
             'course_payment_type' => 'required',
             'price' => $request->course_payment_type === 'Paid' ? 'required|numeric|min:1' : 'nullable|numeric'
        ]);

        if ($request->course_type === 'Offline' && in_array($request->meeting_provider, ['zoom', 'teams', 'google-meet-integration', 'google_meet'])) {

            // Validate based on schedule type
            if ($request->schedule_type && in_array($request->schedule_type, ['daily', 'weekly', 'custom'])) {
                // Schedule-based validation
                if ($request->schedule_type === 'daily') {
                    $request->validate([
                        'daily_time' => 'required',
                        'daily_duration' => 'required|integer|min:1',
                        'daily_repeat' => 'required|in:every_day,weekdays',
                    ]);
                } elseif ($request->schedule_type === 'weekly') {
                    $request->validate([
                        'weekly_days' => 'required|array|min:1',
                        'weekly_days.*' => 'integer|between:0,6',
                        'weekly_time' => 'required',
                        'weekly_duration' => 'required|integer|min:1',
                    ], [
                        'weekly_days.required' => 'Please select at least one day for weekly sessions.',
                    ]);
                } elseif ($request->schedule_type === 'custom') {
                    $request->validate([
                        'custom_dates' => 'required|array|min:1',
                        'custom_dates.*' => 'required|date',
                        'custom_times' => 'required|array|min:1',
                        'custom_times.*' => 'required',
                        'custom_durations' => 'required|array|min:1',
                        'custom_durations.*' => 'required|integer|min:1',
                    ], [
                        'custom_dates.required' => 'Please add at least one session.',
                    ]);
                }
            } else {
                // Original single-meeting validation
                $request->validate([
                    'meeting_start_at' => 'required|date|after:now',
                    'meeting_duration' => 'required|integer|min:1',
                ], [
                    'meeting_start_at.after' => 'Meeting start date must be from the current date and time must be from the current time. Past time is not allowed.',
                ]);

                $teachers = \Auth::user()->isAdmin() ? array_filter((array)$request->input('teachers')) : [\Auth::user()->id];
                $meetingStart = \Carbon\Carbon::parse($request->meeting_start_at);
                $meetingDuration = (int)$request->meeting_duration;
                $meetingEnd = $meetingStart->copy()->addMinutes($meetingDuration);

                $overlappingMeetings = \DB::table('courses')
                    ->join('course_user', 'courses.id', '=', 'course_user.course_id')
                    ->whereIn('course_user.user_id', $teachers)
                    ->whereNotNull('courses.meeting_start_at')
                    ->whereNotNull('courses.meeting_duration')
                    ->where(function ($query) use ($meetingStart, $meetingEnd) {
                        $query->whereRaw('? < DATE_ADD(courses.meeting_start_at, INTERVAL courses.meeting_duration MINUTE)', [$meetingStart])
                              ->whereRaw('? > courses.meeting_start_at', [$meetingEnd]);
                    })
                    ->exists();

                if ($overlappingMeetings) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'meeting_start_at' => ['Overlapping date, time, and duration for the same teacher is not allowed.']
                    ]);
                }
            }
        }
        DB::beginTransaction();

        try {

            if (\Illuminate\Support\Facades\Request::hasFile('video_file')) {
                $file = \Illuminate\Support\Facades\Request::file('video_file');
                $filename = time() . '-' . $file->getClientOriginalName();
                $path = public_path() . '/storage/uploads/';
                $filename = strtolower($filename);
                // $file->move($path, $filename);

                $video_id = strtolower($filename);
                $url = asset('storage/uploads/' . $filename);
                //dd("1");
            }
            if (isset($request->video_file) && !empty($request->video_file)) {
                $filename =  $request->video_file;
                //dd("2");
            } else {
                $filename = time();
                 //dd("3");
            }
            // dd([$request->video_file, $filename]);

            $departments = Department::all();
            $request->all();
            //dd($request->all());

            
            $storage = config('filesystems.default');
            if( $storage == 'local') {
                $request = $this->saveFiles($request);
            } else {
                $request = $this->saveFiles_s3($request);
            }

            $slug = "";
            if (($request->slug == "") || $request->slug == null) {
                $slug = Str::slug($request->title);
            } elseif ($request->slug != null) {
                $slug = $request->slug;
            }

            $uniqueId = uniqid();

            if (!$request->category_id) {
                $defaultCategory = Category::first();   // get any existing category
                $request->merge(['category_id' => $defaultCategory->id]);
            }

            if ($request->course_type !== 'Offline') {
                $request->merge([
                    'meeting_provider' => null,
                    'meeting_id'       => null,
                    'meeting_join_url' => null,
                    'meeting_host_url' => null,
                    'meeting_start_at' => null,
                    'meeting_duration' => null,
                    'meeting_timezone' => null,
                ]);
            }

            $course = Course::create($request->all());

            $course->slug = $uniqueId . '-' . $slug;
            $course->department_id = $request->department_id;
            $course->cms = $request->cms;
            $course->marks_required = $request->marks_required;
            $course->course_code = $request->course_code;
            $course->arabic_title = $request->arabic_title ?? null;
            $course->course_lang = $request->course_lang ?? 'english';
            $course->is_online = $request->course_type ?? 'Online';

            $course->current_step = 'course-added';

            $course->temp_id = $uniqueId;
            $course->save();

            // Course created notification
            try {
                $notificationSettings = app(NotificationSettingsService::class);
                if ($notificationSettings->shouldNotify('courses', 'course_created', 'email')) {
                    CourseNotification::sendCourseCreatedEmail(\Auth::user(), $course);
                    CourseNotification::createCourseCreatedBell(\Auth::user(), $course);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send course created notification: ' . $e->getMessage());
            }

            $course_id = $course->id;

            $course_type = 0; // all courses are set to zero
            if (null !== (Session::get('setvaluesession'))) {
                if ((Session::get('setvaluesession')) == 2) {
                    $course_type = 2;
                } elseif ((Session::get('setvaluesession')) == 3) {
                    $course_type = 3;
                }
            }

            DB::table('courses')->where('id', $course_id)->update(['course_type' => $course_type]);

            //Saving  videos
            if ($request->media_type != "") {
                $model_type = Course::class;
                $model_id = $course->id;
                $size = 0;
                $media = '';
                $url = '';
                $video_id = '';
                $name = $course->title . ' - video';

                if (($request->media_type == 'youtube') || ($request->media_type == 'vimeo')) {
                    $video = $request->video;
                    $url = $video;
                    $video_id = array_last(explode('/', $request->video));
                    $media = Media::where('url', $video_id)
                        ->where('type', '=', $request->media_type)
                        ->where('model_type', '=', 'App\Models\Course')
                        ->where('model_id', '=', $course->id)
                        ->first();
                    $size = 0;
                } elseif ($request->media_type == 'upload') {

                    if ($filename != null) {

                        $media = Media::where('type', '=', $request->media_type)
                            ->where('model_type', '=', 'App\Models\Course')
                            ->where('model_id', '=', $course->id)
                            ->first();

                        if ($media == null) {
                            $media = new Media();
                        }
                        $filename = $request->video_file;
                        $media->model_type = $model_type;
                        $media->model_id = $model_id;
                        $media->name = $name;
                        $media->url = url('storage/uploads/' . $request->video_file);
                        // dd($request->video_file);
                        $media->type = $request->media_type;
                        $media->file_name = $request->video_file;
                        $media->size = 0;
                        $media->save();
                    }
                    // dd($filename);
                    $url = asset('storage/uploads/' . $filename);
                    $media = Media::where('url', $filename)
                        ->where('type', '=', $request->media_type)
                        ->where('model_type', '=', 'App\Models\Course')
                        ->where('model_id', '=', $course->id)
                        ->first();

                    if ($request->video_subtitle && $request->video_subtitle != null) {
                        $subtitle_id = $request->video_subtitle;
                        //var_dump($subtitle_id);
                        $subtitle_url = asset('storage/uploads/' . $subtitle_id);

                        $subtitle = Media::where('url', $subtitle_id)
                            ->where('type', '=', 'subtitle')
                            ->where('model_type', '=', 'App\Models\Course')
                            ->where('model_id', '=', $course->id)
                            ->first();
                        if ($subtitle == null) {
                            $subtitle = new Media();
                            $subtitle->model_type = $model_type;
                            $subtitle->model_id = $model_id;
                            $subtitle->name = $name . ' - subtitle';
                            $subtitle->url = $subtitle_url;
                            $subtitle->type = 'subtitle';
                            $subtitle->file_name = $subtitle_id;
                            $subtitle->size = 0;
                            $subtitle->save();
                        }
                    }
                } elseif ($request->media_type == 'embed') {
                    $url = $request->video;
                    $filename = $course->title . ' - video';
                }

                if ($media == null) {
                    //var_dump([$video_id, $filename]);
                    $media = new Media();
                }
                if ($media) {
                    $media->model_type = $model_type;
                    $media->model_id = $model_id;
                    $media->name = $name;
                    $media->url = $url;
                    $media->type = $request->media_type;
                    $media->file_name = (empty($video_id) ? $filename : $video_id);
                    $media->size = 0;
                    //dd($media);
                    $media->save();
                }
            }




            if ((int)$request->price == 0) {
                $course->price = null;
                $course->save();
            }

            //save course weitage

            $course_module_weight = $request->course_module_weight ?? [];
            $last_module_array = $request->course_module_inc ?? ['QuestionModule'];
            $course->is_paid = $request->course_payment_type === 'Paid' ? 1 : 0;
$course->price = $request->course_payment_type === 'Paid' ? $request->price : null;

            //dd($last_module_array);

            $last_module = end($last_module_array);

            CourseModuleWeightage::create([
                'course_id' => $course->id,
                'minimun_qualify_marks' => $request->marks_required ?? 100,
                'weightage' => [
                    'LessonModule'   => isset($course_module_weight['LessonModule']) ? (int)$course_module_weight['LessonModule'] : 0,
                    'QuestionModule' => isset($course_module_weight['QuestionModule']) ? (int)$course_module_weight['QuestionModule'] : 0,
                    'FeedbackModule' => isset($course_module_weight['FeedbackModule']) ? (int)$course_module_weight['FeedbackModule'] : 0,
                ],
                'module_included' => $last_module_array,
                'last_module' => $last_module,
            ]);

            //dd("jj");

            $teachers = \Auth::user()->isAdmin() ? array_filter((array)$request->input('teachers')) : [\Auth::user()->id];

            $course->teachers()->sync($teachers);

            $internalStudents = \Auth::user()->isAdmin() ? (array)$request->input('internalStudents') : [\Auth::user()->id];
            $externalStudents = \Auth::user()->isAdmin() ? (array)$request->input('externalStudents') : [\Auth::user()->id];

            //dd($internalStudents, $externalStudents);

            $students = array_merge($internalStudents, $externalStudents);
            $course->students()->sync($students);
            // Auto subscribe into courses
            foreach ($students as $id) {
                $data = [
                    'user_id' => $id,
                    'course_id' =>  $course->id,
                    'status' => 1
                ];
                SubscribeCourse::updateOrCreate($data);
            }
            $internalStudents = \App\Models\Auth\User::whereHas('roles', function ($q) {
                $q->where('role_id', 3)->where('employee_type', 'internal');
            })->get()->pluck('name', 'id');

            if($request->course_type == 'Online') {
                $redirect_url = route('admin.lessons.create') . '?course_id=' . $course->id;
            } else {
                $redirect_url = route('admin.test_questions.create') . '?course_id=' . $course->id;
            }

            if ($request->meeting_provider && $request->course_type === 'Offline') {
                // Generate live sessions if schedule_type is set
                if ($request->schedule_type && in_array($request->schedule_type, ['daily', 'weekly', 'custom'])) {
                    $course->schedule_type = $request->schedule_type;
                    if ($request->schedule_type === 'weekly') {
                        $course->schedule_days = $request->weekly_days;
                    }
                    $course->save();

                    $failedCount = $this->generateLiveSessions($course, $request);

                    if ($failedCount > 0) {
                        \Session::flash('flash_danger', "Course saved, but {$failedCount} session(s) failed to create meeting links. Please check your {$request->meeting_provider} credentials in External Apps settings. You can regenerate missing links from the course edit page.");
                    }

                    // Send meeting invites to students for courses with sessions
                    if ($course->liveSessions()->whereNotNull('meeting_link')->count() > 0) {
                        $this->sendMeetingInviteToStudents($course, $students);
                    }
                } else {
                    // Original single-meeting flow
                    $meetingData = $this->createMeetingViaModule(
                        $request->meeting_provider, $request, $course
                    );
                    if ($meetingData) {
                        $course->fill($meetingData)->save();
                        $this->sendMeetingInviteToStudents($course, $students);
                    } else {
                        \Session::flash('flash_danger', 'Course saved successfully, but the meeting provider ('.$request->meeting_provider.') failed to create the meeting. Please verify that your credentials are correct and have the required scopes (e.g. meeting:write:admin for Zoom).');
                    }
                }
            }

            // Trainer assigned notification
            try {
                $notificationSettings = app(NotificationSettingsService::class);
                if ($notificationSettings->shouldNotify('trainers', 'trainer_assigned', 'email')) {
                    foreach ($teachers as $teacherId) {
                        if ($teacherId != \Auth::id()) {
                            $trainer = User::find($teacherId);
                            if ($trainer) {
                                CourseNotification::sendTrainerAssignedEmail($trainer, $course, \Auth::user());
                                CourseNotification::createTrainerAssignedBell($trainer, $course, \Auth::user());
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send trainer assigned notification: ' . $e->getMessage());
            }

            
            //dd($redirect_url);
            
            DB::commit();

            return response()->json(['status' => 'success', 'temp_id' => $uniqueId, 'course_id' => $course->id, 'redirect_url' =>  $redirect_url, 'clientmsg' => 'Added successfully']);

        } catch (Exception $e) {

           DB::rollBack();
           return response()->json(['status' => 'error', 'clientmsg' => $e->getMessage()]);
        }
        

    }


    // public function add_std(){
    //        dd('ho');
    // }


    /**
     * Show the form for editing Course.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('course_edit')) {
            return abort(401);
        }
        $teachers = \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('role_id', 2);
        })->get()->pluck('name', 'id');
        $categories = Category::where('status', '=', 1)->pluck('name', 'id');
        $departments = Department::all();

        $internalStudents = \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('role_id', 3)->where('employee_type', 'internal');
        })->get()->pluck('name', 'id');

        $externalStudents = \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('role_id', 3)->where('employee_type', 'external');
        })->get()->pluck('name', 'id');

        $already_assigned_internal_users = SubscribeCourse::where('course_id', $id)->get()->pluck('user_id');

        $course = Course::with('latestModuleWeightage')->findOrFail($id);
        //dd($course);

        $enabledMeetingProviders = [];
        if (\App\Models\ExternalApp::where('slug', 'zoom')->where('is_enabled', true)->where('is_setup', true)->where('status', 'active')->exists()) {
            $enabledMeetingProviders['zoom'] = 'Zoom';
        }
        if (\App\Models\ExternalApp::where('slug', 'teams')->where('is_enabled', true)->where('is_setup', true)->where('status', 'active')->exists()) {
            $enabledMeetingProviders['teams'] = 'Microsoft Teams';
        }
        if (\App\Models\ExternalApp::where('slug', 'google-meet-integration')->where('is_enabled', true)->where('is_setup', true)->where('status', 'active')->exists()) {
            $enabledMeetingProviders['google-meet-integration'] = 'Google Meet';
        }

        return view('backend.courses.edit', compact('already_assigned_internal_users', 'internalStudents', 'externalStudents', 'course', 'teachers', 'categories', 'departments', 'enabledMeetingProviders'));
    }

    /**
     * Update Course in storage.
     *
     * @param  \App\Http\Requests\UpdateCoursesRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCoursesRequest $request, $id)
    {
        
        if (!Gate::allows('course_edit')) {
            return abort(401);
        }
        $course = Course::findOrFail($id);

        if ($request->course_type !== 'Online') {
            $request->validate([
                'start_date' => 'required|date',
                'expire_at'  => 'required|date|after_or_equal:start_date',
            ]);
        } else {
            $request->validate([
                'start_date' => 'nullable|date',
                'expire_at'  => 'nullable|date|after_or_equal:start_date',
            ]);
        }

        $course->course_lang = $request->course_lang ?? 'english';

        $slug = "";
        if (($request->slug == "") || $request->slug == null) {
            $slug = Str::slug($request->title);
        } elseif ($request->slug != null) {
            $slug = $request->slug;
        }

        $slug_lesson = Course::where('slug', '=', $slug)->where('id', '!=', $course->id)->first();
        if ($slug_lesson != null) {
            return back()->withFlashDanger(__('alerts.backend.general.slug_exist'));
        }

        if ($request->course_type === 'Offline' && in_array($request->meeting_provider, ['zoom', 'teams', 'google-meet-integration', 'google_meet'])) {

            if ($request->schedule_type && in_array($request->schedule_type, ['daily', 'weekly', 'custom'])) {
                // Schedule-based validation
                if ($request->schedule_type === 'daily') {
                    $request->validate([
                        'daily_time' => 'required',
                        'daily_duration' => 'required|integer|min:1',
                        'daily_repeat' => 'required|in:every_day,weekdays',
                    ]);
                } elseif ($request->schedule_type === 'weekly') {
                    $request->validate([
                        'weekly_days' => 'required|array|min:1',
                        'weekly_days.*' => 'integer|between:0,6',
                        'weekly_time' => 'required',
                        'weekly_duration' => 'required|integer|min:1',
                    ], [
                        'weekly_days.required' => 'Please select at least one day for weekly sessions.',
                    ]);
                } elseif ($request->schedule_type === 'custom') {
                    $request->validate([
                        'custom_dates' => 'required|array|min:1',
                        'custom_dates.*' => 'required|date',
                        'custom_times' => 'required|array|min:1',
                        'custom_times.*' => 'required',
                        'custom_durations' => 'required|array|min:1',
                        'custom_durations.*' => 'required|integer|min:1',
                    ], [
                        'custom_dates.required' => 'Please add at least one session.',
                    ]);
                }
            } else {
                // Original single-meeting validation
                $request->validate([
                    'meeting_start_at' => 'required|date|after:now',
                    'meeting_duration' => 'required|integer|min:1',
                ], [
                    'meeting_start_at.after' => 'Meeting start date must be from the current date and time must be from the current time. Past time is not allowed.',
                ]);

                $teachers = \Auth::user()->isAdmin() ? array_filter((array)$request->input('teachers')) : [\Auth::user()->id];
                if(empty($teachers)){
                   $teachers = $course->teachers->pluck('id')->toArray();
                }

                $meetingStart = \Carbon\Carbon::parse($request->meeting_start_at);
                $meetingDuration = (int)$request->meeting_duration;
                $meetingEnd = $meetingStart->copy()->addMinutes($meetingDuration);

                $overlappingMeetings = \DB::table('courses')
                    ->join('course_user', 'courses.id', '=', 'course_user.course_id')
                    ->whereIn('course_user.user_id', $teachers)
                    ->where('courses.id', '!=', $course->id)
                    ->whereNotNull('courses.meeting_start_at')
                    ->whereNotNull('courses.meeting_duration')
                    ->where(function ($query) use ($meetingStart, $meetingEnd) {
                        $query->whereRaw('? < DATE_ADD(courses.meeting_start_at, INTERVAL courses.meeting_duration MINUTE)', [$meetingStart])
                              ->whereRaw('? > courses.meeting_start_at', [$meetingEnd]);
                    })
                    ->exists();

                if ($overlappingMeetings) {
                    return back()->withFlashDanger('Overlapping date, time, and duration for the same teacher is not allowed.')->withInput();
                }
            }
        }



        $storage = config('filesystems.default');
        if( $storage == 'local') {
            $request = $this->saveFiles($request);
        } else {
            $request = $this->saveFiles_s3($request);
        }

        //Saving  videos
        if ($request->media_type != "" || $request->media_type  != null) {
            if ($course->mediavideo) {
                $course->mediavideo->delete();
            }
            $model_type = Course::class;
            $model_id = $course->id;
            $size = 0;
            $media = '';
            $url = '';
            $video_id = '';
            $name = $course->title . ' - video';
            $media = $course->mediavideo;
            if ($media == "") {
                $media = new  Media();
            }
            if ($request->media_type != 'upload') {
                if (($request->media_type == 'youtube') || ($request->media_type == 'vimeo')) {
                    $video = $request->video;
                    $url = $video;
                    $video_id = array_last(explode('/', $request->video));
                    $size = 0;
                } elseif ($request->media_type == 'embed') {
                    $url = $request->video;
                    $filename = $course->title . ' - video';
                }
                $media->model_type = $model_type;
                $media->model_id = $model_id;
                $media->name = $name;
                $media->url = $url;
                $media->type = $request->media_type;
                $media->file_name = $video_id;
                $media->size = 0;
                $media->save();
            }

            if ($request->media_type == 'upload') {


                if ($request->video_file != null) {
                    $media = Media::where('type', '=', $request->media_type)
                        ->where('model_type', '=', 'App\Models\Course')
                        ->where('model_id', '=', $course->id)
                        ->first();

                    if ($media == null) {
                        $media = new Media();
                    }
                    $media->model_type = $model_type;
                    $media->model_id = $model_id;
                    $media->name = $name;
                    $media->url = url('storage/uploads/' . $request->video_file);
                    $media->type = $request->media_type;
                    $media->file_name = $request->video_file;
                    $media->size = 0;
                    $media->save();
                }
            }
        }

        //dd( $course, $request->all());

        if ($request->course_type !== 'Offline') {
            $request->merge([
                'meeting_provider' => null,
                'meeting_id'       => null,
                'meeting_join_url' => null,
                'meeting_host_url' => null,
                'meeting_start_at' => null,
                'meeting_duration' => null,
                'meeting_timezone' => null,
            ]);
        }
        
        $course->update($request->all());

        $course->is_online = $request->course_type ?? 'Online';

        // Handle live session scheduling on update
        if ($request->course_type === 'Offline' && $request->meeting_provider && $request->schedule_type && in_array($request->schedule_type, ['daily', 'weekly', 'custom'])) {
            $course->schedule_type = $request->schedule_type;
            if ($request->schedule_type === 'weekly') {
                $course->schedule_days = $request->weekly_days;
            } else {
                $course->schedule_days = null;
            }
            $course->save();

            $failedCount = $this->generateLiveSessions($course, $request);

            if ($failedCount > 0) {
                \Session::flash('flash_danger', "Course updated, but {$failedCount} session(s) failed to create meeting links. Please check your {$request->meeting_provider} credentials in External Apps settings. You can regenerate missing links using the button below.");
            }
        }

        if (($request->slug == "") || $request->slug == null) {
            $course->slug = Str::slug($request->title);
            $course->save();
        }
        if ((int)$request->price == 0) {
            $course->price = null;
            $course->save();
        }
        $next_btn = $request->submit_btn;
        //dd($next_btn);

        if($course->published == 1) {
             return redirect()->route('admin.courses.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
        }

        if($next_btn == 'Save As Draft') {
            return redirect()->route('admin.courses.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
            //dd("hh");
        } else {

            if($course->current_step == 'course-added') {
                if ($request->course_type === 'Online') {
                    return redirect()
                        ->route('admin.lessons.create', ['course_id' => $course->id])
                        ->withFlashSuccess(trans('alerts.backend.general.updated'));
                } 
                
                
                return redirect()
                    ->route('admin.test_questions.create', ['course_id' => $course->id])
                    ->withFlashSuccess(trans('alerts.backend.general.updated'));
            }

            if($course->current_step == 'lesson-added') {
                return redirect()
                    ->route('admin.test_questions.create', ['course_id' => $course->id])
                    ->withFlashSuccess(trans('alerts.backend.general.updated'));
            }
                
            if($course->current_step == 'question-added') {
                //course-feedback-create
                return redirect()
                    ->route('admin.feedback.create_course_feedback', ['course_id' => $course->id])
                    ->withFlashSuccess(trans('alerts.backend.general.updated'));
            }

        }

    }


    /**
     * Display Course.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Gate::allows('course_view')) {
            return abort(401);
        }
        $teachers = User::get()->pluck('name', 'id');
        $lessons = \App\Models\Lesson::where('course_id', $id)->get();
        $tests = \App\Models\Test::where('course_id', $id)->get();

        $course = Course::findOrFail($id);
        $courseTimeline = $course->courseTimeline()->orderBy('sequence', 'asc')->get();

        return view('backend.courses.show', compact('course', 'lessons', 'tests', 'courseTimeline'));
    }


    /**
     * Remove Course from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('course_delete')) {
            return abort(401);
        }
        $course = Course::findOrFail($id);
        if ($course->students->count() >= 1) {
            return redirect()->route('admin.courses.index')->withFlashDanger(trans('alerts.backend.general.delete_warning'));
        } else {
            $course->delete();
        }


        return redirect()->route('admin.courses.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Course at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (!Gate::allows('course_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Course::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }


    /**
     * Restore Course from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (!Gate::allows('course_delete')) {
            return abort(401);
        }
        $course = Course::onlyTrashed()->findOrFail($id);
        $course->restore();

        return redirect()->route('admin.courses.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Course from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        if (!Gate::allows('course_delete')) {
            return abort(401);
        }

        // Get the trashed course
        $course = Course::onlyTrashed()->findOrFail($id);

        // Get all lesson IDs for this course
        $lessonIds = Lesson::where('course_id', $id)->pluck('id')->toArray();

        // Get all media for lessons
        $lessonFiles = Media::where('model_type', Lesson::class)
            ->whereIn('model_id', $lessonIds)
            ->get();

        // Delete lesson files from storage
        foreach ($lessonFiles as $lessonFile) {
            $filePath = public_path('storage/uploads/' . $lessonFile->file_name);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            $lesson = Lesson::find($lessonFile->model_id);
            if ($lesson && $lesson->lesson_image) {
                $lessonImagePath = public_path('storage/uploads/' . $lesson->lesson_image);
                if (File::exists($lessonImagePath)) {
                    File::delete($lessonImagePath);
                }
            }
        }

        // Delete lesson media records
        Media::where('model_type', Lesson::class)
            ->whereIn('model_id', $lessonIds)
            ->delete();

        // Delete course media if exists
        $courseFile = Media::where('model_type', Course::class)
            ->where('model_id', $id)
            ->first();

        if ($courseFile) {
            $courseFilePath = public_path('storage/uploads/' . $courseFile->file_name);
            if (File::exists($courseFilePath)) {
                File::delete($courseFilePath);
            }
            $courseFile->delete();
        }

        // Delete course image if exists
        if ($course->course_image) {
            $courseImagePath = public_path('storage/uploads/' . $course->course_image);
            if (File::exists($courseImagePath)) {
                File::delete($courseImagePath);
            }
        }

        // Force delete the course and all its lessons
        $course->forceDelete();
        Lesson::where('course_id', $id)->forceDelete();

        return redirect()->route('admin.courses.index')
            ->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Permanently save Sequence from storage.
     *
     * @param  Request
     */
    public function saveSequence(Request $request)
    {
        if (!Gate::allows('course_edit')) {
            return abort(401);
        }

        foreach ($request->list as $item) {
            $courseTimeline = CourseTimeline::find($item['id']);
            $courseTimeline->sequence = $item['sequence'];
            $courseTimeline->save();
        }

        return 'success';
    }


    /**
     * Publish / Unpublish courses
     *
     * @param  Request
     */
    public function publish($id)
{
    if (!Gate::allows('course_edit')) {
        return abort(401);
    }

    $course = Course::findOrFail($id);

    // Toggle logic
    if ($course->published == 1) {
        $course->published = -1; // Unpublish
    } else {
        $course->published = 1; // Publish (works for draft & unpublished)
    }

    $course->save();

    return back()->withFlashSuccess(trans('alerts.backend.general.updated'));
}

    public function course_detail($course_id, $employee_id)
    {
        return view('backend.courses.employee_course', ['course_id' => $course_id, 'employee_id' => $employee_id]);
    }

    public function get_data_employee_course(Request $request, $course_id, $employee_id)
    {
        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $teachers = "";



        $teachers = Lesson::query()
            ->select('lessons.*')
            ->where('lessons.course_id', $course_id)
            ->orderBy('lessons.position');


        if (auth()->user()->isAdmin()) {
            $has_view = true;
            $has_edit = true;
            $has_delete = true;
        }
        //$teachers = $teachers->get();
        //dd($teachers);

        return DataTables::of($teachers)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $course_id, $request) {
                $view = "";
                $edit = "";
                $delete = "";

                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.employee', 'label' => 'id', 'value' => $q->id]);
                }

                if ($has_view) {
                    $view = view('backend.datatable.action-view')
                        ->with(['route' => route('admin.employee.course_detail', [$course_id, $q->id])])->render();
                }

                if ($has_edit) {

                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.employee.edit', ['id' => $q->id])])
                        ->render();
                    $view .= $edit;
                }

                if ($has_delete) {

                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.employee.destroy', ['id' => $q->id])])
                        ->render();
                    $view .= $delete;
                }

                //$view .= '<a class="btn btn-warning mb-1" href="' . route('admin.courses.index', ['teacher_id' => $q->id]) . '">' . trans('labels.backend.courses.title') . '</a>';

                return $view;
            })
            ->addColumn('status', function ($q) use ($course_id, $employee_id, $request) {
                $status = CustomHelper::lessonStatusEmployee($q->duration, $q->id, $course_id, $employee_id);
                if ($status == 'Completed') {
                    $status = '<span class="badge badge-success">' . $status . '</span>';
                } elseif ($status == 'In progress') {
                    $status = '<span class="badge badge-info">' . $status . '</span>';
                } else {
                    $status = '<span class="badge badge-warning">' . $status . '</span>';
                }

                return $status;
            })
            ->addColumn('attendance', function ($q) use ($course_id, $employee_id, $request) {
                return CustomHelper::ck_attendance_lesson($course_id, $q->id, $employee_id) ? '<span class="badge badge-success">Present</span>' : '';
            })
            ->addColumn('lesson_start_date', function ($q) use ($course_id, $employee_id, $request) {
                $lesson_data = CustomHelper::getLessonDetail($q->id, $course_id, $employee_id);
                return $lesson_data ? $lesson_data->created_at : '-';
            })
            ->addColumn('lesson_end_date', function ($q) use ($course_id, $employee_id, $request) {
                $lesson_data = CustomHelper::getLessonDetail($q->id, $course_id, $employee_id);
                return $lesson_data ? $lesson_data->created_at : '-';
            })
            ->rawColumns(['actions', 'attendance', 'image', 'status', 'lesson_start_date', 'lesson_end_date'])
            ->make();
    }

    public function exportCourseAsCsv()
    {
        ini_set('max_execution_time', 300);
        return Excel::download(new CoursesExport, 'courses.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function recordAttendance($slug)
    {
        $course = Course::where('slug', $slug)->first();
        $user_id = auth()->id();

        //dd($course);
        
        if ($course->is_online_course) {
            abort(403, 'Sorry! this course is not eligible for offline attendance');
        }

        $assigned = courseAssignment::whereRaw("FIND_IN_SET(?, assign_to)", [$user_id])->where([
            'course_id' => $course->id,
        ])->first();

        if(!$assigned) {
            //dd("yes not assingned");
            $latest_assignment = courseAssignment::where('course_id', $course->id)
                ->orderBy('id', 'desc')  // safer than latest()
                ->first();

            $assigned = courseAssignment::create([
                'course_id'           => $course->id,
                'assign_by'           => $user_id,
                'assign_to'           => $user_id,
                'assign_date'         => date('Y-m-d'),

                // If latest assignment exists → reuse its values, else defaults
                'due_date'            => optional($latest_assignment)->due_date,
                'is_pathway'          => optional($latest_assignment)->is_pathway ?? 0,
                'by_invitation'       => 1,
                'meeting_link'        => optional($latest_assignment)->meeting_link,
                'classroom_location'  => optional($latest_assignment)->classroom_location,
                'meeting_end_datetime'=> optional($latest_assignment)->meeting_end_datetime,
            ]);

            $assigned_to_user = CourseAssignmentToUser::create(
                [
                    'created_at' => date('Y-m-d H:i:s'),
                    'course_assignment_id' => $assigned->id,
                    'course_id' => $course->id,
                    'user_id' => $user_id,
                    'log_comment' => 'manually asigned to him/her',
                    'updated_at' => date('Y-m-d H:i:s'),
                    'by_pathway' => optional($latest_assignment)->is_pathway ?? 0,
                    'by_invitation' => 1
                ]
            );

            $has_assesment = 0;
            $has_feedback = CourseFeedback::query()
                                    ->where('course_id', $course->id)
                                    ->count() ?? 0;

            $agmt = Assignment::where('assignments.course_id', $course->id)
                                    ->join('courses', 'courses.id', '=', 'assignments.course_id')
                                    ->join('course_assignment', 'course_assignment.course_id', '=', 'courses.id')
                                    ->join('tests', 'tests.id', '=', 'assignments.test_id')
                                    ->join('test_questions', 'test_questions.test_id', '=', 'tests.id')
                                    //->whereRaw('FIND_IN_SET(?, `assign_to`) > 0', $logged_in_user_id)
                                    ->exists();
            if ($agmt) {
                $has_assesment = true;
            } 

            $sub_data = SubscribeCourse::updateOrCreate([
            'user_id' => $user_id,
            'course_id' => $course->id,
            ],
            [ 
                'has_assesment' => $has_assesment ?? 0,
                'has_feedback' => $has_feedback > 0 ? 1 : 0,
                'status' => 1,
                'assign_date' => date('Y-m-d'),
                'due_date' => $assigned->due_date,
                'is_attended' => 1,
                'by_invitation' => 1
            ]);
        }
        //dd($assigned, $user_id, $assigned_to_user);
        

        $name = auth()->user()->full_name;
        $message = "Dear <b>$name</b>,</br> Thank you for attending <b>$course->title</b> course with Delta academy. Your attendance has been recorded successfully.";

        $due_date = null;
        if ($assigned) {
            $due_date = $assigned->due_date;
            $message = "Dear <b>$name</b>,</br> Your attendance has already been recorded for the course <b>$course->title</b>.";
        }

        
        if (!$assigned) {

            courseAssignment::create([
                'course_id' => $course->id,
                'assign_to' => $user_id,
                'assign_date' => date('Y-m-d')
            ]);

        } 


        


        SubscribeCourse::updateOrCreate([
            'user_id' => $user_id,
            'course_id' => $course->id,
        ],
        [ 
            
            'status' => 1,
            'assign_date' => date('Y-m-d'),
            //'due_date' => $due_date,
            'is_attended' => 1,
            //'is_completed' => 1,
            //'completed_at' => date('Y-m-d H:i:s')
        ]);

        $progress_status = 'Not started';
        $progress = 0;

        $progress = CustomHelper::progress($course->id, $user_id);
        //dd($progress, "jj");
        
        if ($progress > 0 && $progress < 75) {
            $progress_status =  'In progress';
        } elseif ($progress >= 75) {
            $progress_status =  'Completed';
        }

        // make an entry
        SubscribeCourse::updateOrCreate([
            'user_id' => $user_id,
            'course_id' => $course->id,
        ],
        [   
            
            'assignment_progress' =>  $progress,
            'course_trainer_name' => CustomHelper::getCourseTrainerName($course->id) ?? null,
            'status' => 1,
            'assign_date' => date('Y-m-d'),
            //'due_date' => null,
        ]);

        $sub_detail = SubscribeCourse::where([
            'user_id' => $user_id,
            'course_id' => $course->id,
        ])->first();

        if($sub_detail) {
            if($sub_detail->has_assesment == 0 && $sub_detail->has_feedback == 0) {
                SubscribeCourse::updateOrCreate([
                    'user_id' => $user_id,
                    'course_id' => $course->id,
                ],
                [ 
                    
                    'status' => 1,
                    'assign_date' => date('Y-m-d'),
                    'is_attended' => 1,
                    'is_completed' => 1,
                    'grant_certificate' => 1,
                    'completed_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        return redirect("/course/$course->slug");
        // return view('frontend.offline-attendance-recorded', ['course_name' => $course->title, 'message' => $message]);
    }

    public function exportCourseAssignmentReportAsCsv()
    {
        ini_set('max_execution_time', 300);
        return Excel::download(new CourseAssignmentReportExport, 'course-assignment-report.csv');
    }

    private function generateLiveSessions(Course $course, Request $request): int
    {
        // Delete any existing sessions for this course
        $course->liveSessions()->delete();

        $provider = $request->meeting_provider;
        $timezone = $request->meeting_timezone ?? 'Asia/Riyadh';
        $scheduleType = $request->schedule_type;

        $startDate = \Carbon\Carbon::parse($course->getRawOriginal('start_date'));
        $endDate = \Carbon\Carbon::parse($course->getRawOriginal('expire_at'));

        $sessions = [];

        if ($scheduleType === 'daily') {
            $time = $request->daily_time;
            $duration = (int)($request->daily_duration ?? 60);
            $repeat = $request->daily_repeat ?? 'every_day';

            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                $isWeekday = $current->isWeekday();
                if ($repeat === 'every_day' || ($repeat === 'weekdays' && $isWeekday)) {
                    $sessions[] = [
                        'date' => $current->format('Y-m-d'),
                        'time' => $time,
                        'duration' => $duration,
                    ];
                }
                $current->addDay();
            }
        } elseif ($scheduleType === 'weekly') {
            $time = $request->weekly_time;
            $duration = (int)($request->weekly_duration ?? 60);
            $selectedDays = array_map('intval', $request->weekly_days ?? []);

            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                if (in_array($current->dayOfWeek, $selectedDays)) {
                    $sessions[] = [
                        'date' => $current->format('Y-m-d'),
                        'time' => $time,
                        'duration' => $duration,
                    ];
                }
                $current->addDay();
            }
        } elseif ($scheduleType === 'custom') {
            $dates = $request->custom_dates ?? [];
            $times = $request->custom_times ?? [];
            $durations = $request->custom_durations ?? [];

            foreach ($dates as $i => $date) {
                if (!$date || !isset($times[$i])) continue;
                $sessionDate = \Carbon\Carbon::parse($date);
                if ($sessionDate->lt($startDate) || $sessionDate->gt($endDate)) continue;

                $sessions[] = [
                    'date' => $date,
                    'time' => $times[$i],
                    'duration' => (int)($durations[$i] ?? 60),
                ];
            }
        }

        $lastSessionDate = null;
        $failedCount = 0;

        foreach ($sessions as $session) {
            $sessionDateTime = $session['date'] . ' ' . $session['time'] . ':00';

            // Create meeting via provider for each session
            $meetingLink = null;
            $meetingId = null;
            $hostUrl = null;

            $meetingRequest = new Request();
            $meetingRequest->merge([
                'meeting_start_at' => $sessionDateTime,
                'meeting_duration' => $session['duration'],
                'meeting_timezone' => $timezone,
            ]);

            try {
                $meetingData = $this->createMeetingViaModule($provider, $meetingRequest, $course);
                if ($meetingData) {
                    $meetingLink = $meetingData['meeting_join_url'] ?? null;
                    $meetingId = $meetingData['meeting_id'] ?? null;
                    $hostUrl = $meetingData['meeting_host_url'] ?? null;
                }
                if (!$meetingLink) {
                    $failedCount++;
                }
            } catch (\Throwable $e) {
                \Log::warning("Failed to create meeting for session {$session['date']}: " . $e->getMessage());
                $failedCount++;
            }

            LiveSession::create([
                'course_id' => $course->id,
                'provider' => $provider,
                'session_date' => $session['date'],
                'session_time' => $session['time'],
                'meeting_link' => $meetingLink,
                'meeting_id' => $meetingId,
                'host_url' => $hostUrl,
                'duration' => $session['duration'],
                'recurrence_type' => $scheduleType,
                'created_by' => \Auth::id(),
            ]);

            $lastSessionDate = $session['date'];
        }

        // Update course with last session date
        if ($lastSessionDate) {
            $course->last_session_date = $lastSessionDate;
            $course->save();
        }

        return $failedCount;
    }

    /**
     * Regenerate meeting links for sessions that have null meeting_link.
     */
    public function regenerateMeetingLinks($courseId)
    {
        if (!Gate::allows('course_edit')) {
            return abort(401);
        }

        $course = Course::findOrFail($courseId);
        $sessions = $course->liveSessions()->whereNull('meeting_link')->get();

        if ($sessions->isEmpty()) {
            return back()->withFlashSuccess('All sessions already have meeting links.');
        }

        $provider = $course->meeting_provider;
        $timezone = $course->meeting_timezone ?? 'Asia/Riyadh';
        $successCount = 0;
        $failedCount = 0;

        foreach ($sessions as $session) {
            $timeFormatted = \Carbon\Carbon::parse($session->session_time)->format('H:i:s');
            $sessionDateTime = $session->session_date->format('Y-m-d') . ' ' . $timeFormatted;

            $meetingRequest = new Request();
            $meetingRequest->merge([
                'meeting_start_at' => $sessionDateTime,
                'meeting_duration' => $session->duration,
                'meeting_timezone' => $timezone,
            ]);

            try {
                $meetingData = $this->createMeetingViaModule($provider, $meetingRequest, $course);
                if ($meetingData) {
                    $session->update([
                        'meeting_link' => $meetingData['meeting_join_url'] ?? null,
                        'meeting_id' => $meetingData['meeting_id'] ?? null,
                        'host_url' => $meetingData['meeting_host_url'] ?? null,
                    ]);
                    $successCount++;
                } else {
                    $failedCount++;
                }
            } catch (\Throwable $e) {
                \Log::warning("Failed to regenerate meeting for session {$session->id}: " . $e->getMessage());
                $failedCount++;
            }
        }

        if ($failedCount > 0) {
            return back()->withFlashDanger("Regenerated {$successCount} meeting link(s), but {$failedCount} failed. Please check your {$provider} credentials in External Apps settings.");
        }

        return back()->withFlashSuccess("Successfully regenerated {$successCount} meeting link(s).");
    }

    private function createMeetingViaModule(string $provider, Request $request, Course $course): ?array
    {
        if ($provider === 'zoom') {
            $service = new \Modules\Zoom\Services\ZoomMeetingService();
            $meeting = $service->createMeeting(
                $course->title,
                $request->meeting_start_at,
                $request->meeting_duration,
                $request->meeting_timezone
            );

            if ($meeting) {
                return [
                    'meeting_id'       => $meeting['id'],
                    'meeting_join_url' => $meeting['join_url'],
                    'meeting_host_url' => $meeting['host_url'] ?? null,
                ];
            }
        } elseif ($provider === 'teams') {
            $service = new \Modules\Teams\Services\TeamsMeetingService();
            $meeting = $service->createMeeting(
                $course->title,
                $request->meeting_start_at,
                $request->meeting_duration,
                $request->meeting_timezone
            );

            if ($meeting) {
                return [
                    'meeting_id'       => $meeting['id'],
                    'meeting_join_url' => $meeting['join_url'],
                    'meeting_host_url' => $meeting['host_url'] ?? null,
                ];
            }
        } elseif (in_array($provider, ['google-meet-integration', 'google_meet'])) {
            $service = new \Modules\GoogleMeetIntegration\Services\GoogleMeetService();
            
            $hostEmail = null;
            $teacherEmails = $course->teachers->pluck('email')->toArray();
            if (!empty($teacherEmails)) {
                $hostEmail = $teacherEmails[0];
            }

            $studentEmails = $course->students->pluck('email')->toArray();
            $attendees = array_merge($teacherEmails, $studentEmails);

            $meeting = $service->createMeeting(
                $course->title,
                $request->meeting_start_at,
                $request->meeting_duration,
                $request->meeting_timezone,
                $hostEmail,
                $attendees
            );

            if ($meeting) {
                return [
                    'meeting_id'       => $meeting['id'],
                    'meeting_join_url' => $meeting['join_url'],
                    'meeting_host_url' => $meeting['host_url'] ?? null,
                ];
            }
        }
        return null;
    }

    private function sendMeetingInviteToStudents(Course $course, array $studentIds): void
    {
        $students = User::whereIn('id', $studentIds)->get();

        // Google Meet integration: Add as attendee if course has a meeting
        if ($course->meeting_provider == 'google-meet-integration' && $course->meeting_id) {
            $service = new \Modules\GoogleMeetIntegration\Services\GoogleMeetService();
            $hostEmail = $course->teachers->first()->email ?? null;
            foreach ($students as $student) {
                $service->addAttendeeToMeeting($course->meeting_id, $student->email, $hostEmail);
            }
        }

        foreach ($students as $student) {
            \Illuminate\Support\Facades\Mail::to($student->email)
                ->send(new \App\Mail\CourseMeetingInvite($course));
        }
    }

}
