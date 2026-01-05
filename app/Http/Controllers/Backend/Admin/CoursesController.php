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
use App\Models\{Assignment, courseAssignment, CourseAssignmentToUser, CourseFeedback, Department};
use App\Models\Stripe\SubscribeCourse;
use App\Jobs\SendEmailJob;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use Session;
use App\Exports\CourseAssignmentReportExport;

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

        return view('backend.courses.index');
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
                ->whereHas('category')
                ->ofTeacher()->orderBy('created_at', 'desc');
        } elseif (request('teacher_id') != "") {
            $id = request('teacher_id');
            $courses = Course::query()->ofTeacher()
                ->whereHas('category')
                ->whereHas('teachers', function ($q) use ($id) {
                    $q->where('course_user.user_id', '=', $id);
                })->orderBy('created_at', 'desc');
        } elseif (request('cat_id') != "") {
            $id = request('cat_id');
            $courses = Course::query()->ofTeacher()
                ->whereHas('category')
                ->where('category_id', '=', $id)->orderBy('created_at', 'desc');
        } else {
            $courses = Course::query()->ofTeacher()
                ->whereHas('category')
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
            ->addColumn('status', function ($q) {
                $text = "";
                $text = ($q->published == 1) ? "<p class='text-white mb-1 font-weight-bold text-center bg-dark p-1 mr-1' >" . trans('labels.backend.courses.fields.published') . "</p>" : "<p class='text-white mb-1 font-weight-bold text-center bg-primary p-1 mr-1' >" . trans('labels.backend.courses.fields.unpublished') . "</p>";
                if (auth()->user()->isAdmin()) {
                    $text .= ($q->featured == 1) ? "<p class='text-white mb-1 font-weight-bold text-center bg-warning p-1 mr-1' >" . trans('labels.backend.courses.fields.featured') . "</p>" : "";
                    $text .= ($q->trending == 1) ? "<p class='text-white mb-1 font-weight-bold text-center bg-success p-1 mr-1' >" . trans('labels.backend.courses.fields.trending') . "</p>" : "";
                    $text .= ($q->popular == 1) ? "<p class='text-white mb-1 font-weight-bold text-center bg-primary p-1 mr-1' >" . trans('labels.backend.courses.fields.popular') . "</p>" : "";
                }

                return $text;
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
        $courses = "";

        if (request('show_deleted') == 1) {
            if (!Gate::allows('course_delete')) {
                return abort(401);
            }
            $courses = Course::query()->onlyTrashed()
                ->whereHas('category')
                ->ofTeacher()->orderBy('created_at', 'desc');
        } elseif (request('teacher_id') != "") {
            $id = request('teacher_id');
            $courses = Course::query()->ofTeacher()
                ->whereHas('category')
                ->whereHas('teachers', function ($q) use ($id) {
                    $q->where('course_user.user_id', '=', $id);
                })->orderBy('created_at', 'desc');
        } elseif (request('cat_id') != "") {
            $id = request('cat_id');
            $courses = Course::query()->ofTeacher()
                ->whereHas('category')
                ->where('category_id', '=', $id)->orderBy('created_at', 'desc');
        } else {
            $courses = Course::query()
                ->whereHas('category')
                ->orderBy('created_at', 'desc');
            //dd("jlj");
        }

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
            // ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
            //     $view = "";
            //     $edit = "";
            //     $delete = "";
            //     if ($request->show_deleted == 1) {
            //         return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.courses', 'label' => 'id', 'value' => $q->id]);
            //     }
            //     if ($has_view) {
            //         $view = view('backend.datatable.action-view')
            //             ->with(['route' => route('admin.courses.show', ['course' => $q->id])])->render();
            //     }
            //     if ($has_edit) {
            //         $edit = view('backend.datatable.action-edit')
            //             ->with(['route' => route('admin.courses.edit', ['course' => $q->id])])
            //             ->render();
            //         $view .= $edit;
            //     }

            //     if ($has_delete) {
            //         $delete = view('backend.datatable.action-delete')
            //             ->with(['route' => route('admin.courses.destroy', ['course' => $q->id])])
            //             ->render();
            //         $view .= $delete;
            //     }
            //     if ($q->published == 1) {
            //         $type = 'action-unpublish';
            //     } else {
            //         $type = 'action-publish';
            //     }

            //     $view .= view('backend.datatable.' . $type)
            //         ->with(['route' => route('admin.courses.publish', ['id' => $q->id])])->render();

            //     if (!$q->is_online_course) {
            //         $copy_offline_link = view('backend.datatable.copy-offline-link')
            //             ->with(['route' => route('coursePreview', ['slug' => $q->slug])])
            //             ->render();
            //         $view .= $copy_offline_link;
            //     }
            //     return $view;
            // })
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
        $actions = '<div class="action-pill">';

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

    $actions .= '</div>';

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
            
                <a class="btn btn-outline-success" href="' . route('admin.lessons.create', ['course_id' => $q->id]) . '">
                    <i class="fa fa-plus-circle" aria-hidden="true" style="font-size:20px"></i>
                </a>
                <a class="add-btn" style="padding:7px 20px 11px 20px" href="' . route('admin.lessons.index', ['course_id' => $q->id]) . '">
                    <i class="fa fa-eye" aria-hidden="true" style="font-size:18px margin-bottom:-3px"></i>
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
        return '<a class="btn btn-outline-success" href="' . route('admin.tests.create', ['course_id' => $q->id]) . '">
              <i class="fa fa-plus-square" aria-hidden="true" style="font-size:20px;margin-bottom:-3px"></i>  <span style="">
                   Add Test
                   </span>
                </a>';
    } else {
        return '<a class="add-btn" style="padding:7px 20px 11px 20px" href="' . route('admin.tests.index', ['course_id' => $q->id]) . '">
                  <span style="">
                   (' . $total_tests . ') 
                   </span>  <i class="fa fa-eye ml-1" aria-hidden="true" style="font-size:13px;margin-bottom:-1px"></i> 
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
            <i class="fa fa-qrcode ml-3" style="font-size:20px;color:#3c4085"></i>
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
                $text = "";
                $text = ($q->published == 1) ? "<p class='pill-publish' >" . trans('labels.backend.courses.fields.published') . "</p>" : "<p class='pill-unpublish' >" . trans('labels.backend.courses.fields.unpublished') . "</p>";
                if (auth()->user()->isAdmin()) {
                    $text .= ($q->featured == 1) ? "<p class='text-white mb-1 font-weight-bold text-center bg-warning p-1 mr-1' >" . trans('labels.backend.courses.fields.featured') . "</p>" : "";
                    $text .= ($q->trending == 1) ? "<p class='text-white mb-1 font-weight-bold text-center bg-success p-1 mr-1' >" . trans('labels.backend.courses.fields.trending') . "</p>" : "";
                    $text .= ($q->popular == 1) ? "<p class='text-white mb-1 font-weight-bold text-center bg-primary p-1 mr-1' >" . trans('labels.backend.courses.fields.popular') . "</p>" : "";
                }

                return $text;
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

                return '<a class="add-btn" style="padding:7px 20px 11px 20px"  href="' . route('admin.enrolled_student', ['course_id' => $q->id]) . '"> (' . CustomHelper::totalEnrolled($q->id) . ') <i class="fa fa-eye ml-1" aria-hidden="true"></i> </a>';
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
            ->rawColumns(['teachers', 'assignment', 'department', 'duration', 'total_students_enrolled', 'tests', 'lessons', 'course_image', 'actions', 'status','qr_code'])
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

        return view('backend.courses.create', compact('internalStudents', 'externalStudents', 'teachers', 'categories', 'departments'));
    }

    /**
     * Store a newly created Course in storage.
     *
     * @param  \App\Http\Requests\StoreCoursesRequest $request
     * @return \Illuminate\Http\Response
     */

    public function store(StoreCoursesRequest $request)
    {
        //dd("yes");
        $media = '';
        if (!Gate::allows('course_create')) {
            return abort(401);
        }
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
        ]);
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
                $slug = str_slug($request->title);
            } elseif ($request->slug != null) {
                $slug = $request->slug;
            }

            $uniqueId = uniqid();

            $course = Course::create($request->all());
            $course->slug = $uniqueId . '-' . $slug;
            $course->department_id = $request->department_id;
            $course->cms = $request->cms;
            $course->marks_required = $request->marks_required;
            $course->course_code = $request->course_code;
            $course->arabic_title = $request->arabic_title ?? null;
            $course->course_lang = $request->course_lang ?? 'english';
            $course->is_online = $request->course_type ?? 'Online';

            

            $course->temp_id = $uniqueId;
            $course->save();

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

        $course = Course::findOrFail($id);

        return view('backend.courses.edit', compact('already_assigned_internal_users', 'internalStudents', 'externalStudents', 'course', 'teachers', 'categories', 'departments'));
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
        $course->course_lang = $request->course_lang ?? 'english';
        
        $slug = "";
        if (($request->slug == "") || $request->slug == null) {
            $slug = str_slug($request->title);
        } elseif ($request->slug != null) {
            $slug = $request->slug;
        }

        $slug_lesson = Course::where('slug', '=', $slug)->where('id', '!=', $course->id)->first();
        if ($slug_lesson != null) {
            return back()->withFlashDanger(__('alerts.backend.general.slug_exist'));
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

        //dd($request->all());

        $course->update($request->all());
        if (($request->slug == "") || $request->slug == null) {
            $course->slug = str_slug($request->title);
            $course->save();
        }
        if ((int)$request->price == 0) {
            $course->price = null;
            $course->save();
        }

        $teachers = \Auth::user()->isAdmin() ? array_filter((array)$request->input('teachers')) : [\Auth::user()->id];
        $course->teachers()->sync($teachers);

        $internalStudents = \Auth::user()->isAdmin() ? (array)$request->input('internalStudents') : [\Auth::user()->id];
        $externalStudents = \Auth::user()->isAdmin() ? (array)$request->input('externalStudents') : [\Auth::user()->id];
        //dd($internalStudents);

        $students = array_merge($internalStudents, $externalStudents);
        // Auto subscribe into courses
        foreach ($students as $id) {
            $data = [
                'user_id' => $id,
                'course_id' =>  $course->id,
                'status' => 1
            ];
            SubscribeCourse::updateOrCreate($data);
        }

        return redirect()->route('admin.courses.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
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
        if ($course->published == 1) {
            $course->published = 0;
        } else {
            $course->published = 1;
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
}
