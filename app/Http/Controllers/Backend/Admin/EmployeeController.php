<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Exceptions\GeneralException;
use App\Exports\InternalAttendanceReportExport;
use App\Exports\TraineesExport;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRequest;
use App\Models\Auth\User;
use App\Models\EmployeeProfile;
use App\Models\Reports;
use App\Models\Stripe\SubscribeCourse;
use App\Models\{Department, ChapterStudent, Certificate, UserCourseDetail, Course, ExportInternalReportNotification, PasswordReset, Test};
use App\Models\Position;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;
use DB;
use Carbon\Carbon;
use App\Helpers\CustomHelper;
use Maatwebsite\Excel\Facades\Excel;
use Config;
use App\Imports\UsersImport;
use App\Jobs\GenerateInternalAttendanceReport;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Facades\Hash;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Mail;
use App\Mail\Backend\LiveLesson\TeacherMeetingSlotMail;
use App\Mail\ResetMail;
use Illuminate\Support\Str;
use PDF;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class EmployeeController extends Controller
{
    use FileUploadTrait;

    
    /**
     * Display a listing of Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //dd("fghff");
        $status = $request->get('status');
        return view('backend.employee.index', [
            'status' => $status
        ]);
    }

    public function externalIndex()
    {
        return view('backend.employee.external_index');
    }

    /**
     * Display a listing of Courses via ajax DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function getExternalData(Request $request)
    {
        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $teachers = "";


        if (request('show_deleted') == 1) {
            $teachers = User::query()->role('student')->where('employee_type', 'external')->onlyTrashed()->orderBy('created_at', 'desc');
        } else {
            $teachers = User::query()->role('student')->where('employee_type', 'external')->orderBy('created_at', 'desc');
        }

        if (auth()->user()->isAdmin()) {
            $has_view = true;
            $has_edit = true;
            $has_delete = true;
        }

        //dd("ghjjg");
        $has_view   = Gate::allows('trainee_view');
        $has_edit   = Gate::allows('trainee_edit');
        $has_delete = Gate::allows('trainee_delete');

        return DataTables::of($teachers)
            ->addIndexColumn()
           ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
        if ($request->show_deleted == 1) {
            return view('backend.datatable.action-trashed')->with([
                'route_label' => 'admin.employee',
                'label' => 'id',
                'value' => $q->id
            ]);
        }

        $view = $edit = $delete = '';

        if ($has_view) {
            $view = view('backend.datatable.action-view')
                ->with(['route' => route('admin.employee.show', ['id' => $q->id])])
                ->render();
        }

        if ($has_edit) {
            $edit = view('backend.datatable.action-edit')
                ->with(['route' => route('admin.employee.edit', ['id' => $q->id])])
                ->render();
        }

        if ($has_delete) {
            $delete = view('backend.datatable.action-delete')
                ->with(['route' => route('admin.employee.destroy', ['id' => $q->id])])
                ->render();
        }

        return '<div class="action-pill">' . $view . $edit . $delete .'</div>';
        })
                ->addColumn('department', function ($q) {
                    $deaprt = $q->getDepartment();
                    return $deaprt;
                })
            ->addColumn('position', function ($q) {
                $deaprt = $q->getPosition();
                return $deaprt;
            })
            // ->addColumn('status', function ($q) {
            //     $checked = $q->active == 1 ? 'checked' : '';
            //     $html = '<label class="switch switch-lg switch-3d switch-primary">
            //                 <input type="checkbox" id="' . $q->id . '" class="switch-input" data-id="' . $q->id . '" value="1" checked="' . $checked . '">
            //                 <span class="switch-label"></span>
            //                 <span class="switch-handle"></span>
            //             </label>
            //             ';
            //     return $html;
            //     // return ($q->active == 1) ? "Enabled" : "Disabled";
            // })
                     ->addColumn('status', function ($q) {
        $checked = $q->active == 1 ? 'checked' : '';
        $html = '<div class="custom-control custom-switch">
                    <input class="custom-control-input status-toggle" type="checkbox" role="switch"
                        id="switch' . $q->id . '" data-id="' . $q->id . '" ' . $checked . '>
                    <label class="custom-control-label" for="switch' . $q->id . '"></label>
                </div>';
        return $html;
        })
            
            ->rawColumns(['actions', 'department', 'position', 'image', 'status'])
            ->make();
    }

    /**
     * Display a listing of Courses via ajax DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {

        //dd("fgf");
        $status = $request->get('status');
        
        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $teachers = "";
        $has_reset = false;

        if (request('show_deleted') == 1) {
            $teachers = User::query()->role('student')
                ->where('employee_type', 'internal')
                ->when($status == 'active', function ($q) {
                    $q->where('active', '1');
                })
                ->onlyTrashed()
                ->groupBy('email')
                ->orderBy('created_at', 'desc');
        } else {
            $teachers = User::query()->role('student')->where('employee_type', 'internal')
            ->when($status == 'active', function ($q) {
                    $q->where('active', '1');
            })
            ->groupBy('email')->orderBy('created_at', 'desc');
        }

        

        if (auth()->user()->isAdmin()) {
            $has_view = true;
            $has_edit = true;
            $has_delete = true;
            $has_reset = true;
        }
        

        $has_view   = Gate::allows('trainee_view');
        $has_edit   = Gate::allows('trainee_edit');
        $has_delete = Gate::allows('trainee_delete');
        //$has_reset = true;


        return DataTables::of($teachers)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $has_reset, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                $reset = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.employee', 'label' => 'id', 'value' => $q->id]);
                }

                if ($has_view) {
                    $view = view('backend.datatable.action-view')
                        ->with(['route' => route('admin.employee.show', ['id' => $q->id])])->render();
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

                if ($has_reset) {
                    $reset = view('backend.datatable.action-reset-password')
                        ->with(['route' => route('admin.employee.reset-pass', ['id' => $q->id]), 'email' => $q->email])
                        ->render();
                    $view .= $reset;
                }

                //$view .= '<a class="btn btn-warning mb-1" href="' . route('admin.courses.index', ['teacher_id' => $q->id]) . '">' . trans('labels.backend.courses.title') . '</a>';

                return $view;
            })
            ->addColumn('department', function ($q) {
                $deaprt = $q->getDepartment();
                return $deaprt;
            })
            ->addColumn('position', function ($q) {
                $deaprt = $q->getPosition();
                return $deaprt;
            })
            ->addColumn('qr_code', function ($q) {
                return QrCode::size(80)->generate(url('/'));
            })
            ->addColumn('status', function ($q) {
                $checked = $q->active == 1 ? 'checked' : '';
                $html = '<label class="switch switch-lg switch-3d switch-primary">
                            <input type="checkbox" id="' . $q->id .'" class="switch-input" data-id="' . $q->id .'" ' . $checked  .'>
                            <span class="switch-label"></span>
                            <span class="switch-handle"></span>
                        </label>
                        ';
                return $html;
                // return ($q->active == 1) ? "Enabled" : "Disabled";
            })
            ->rawColumns(['actions', 'department', 'position', 'image', 'status'])
            ->make();
    }

    /**
     * Show the form for creating new Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $departments = Department::all();
        $positions = Position::all();
        return view('backend.employee.create', ['departments' => $departments, 'positions' => $positions]);
    }

    /**
     * Store a newly created Category in storage.
     *
     * @param  \App\Http\Requests\StoreTeachersRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEmployeeRequest $request)
    {
        //$request = $this->saveFiles($request);
        //dd($request->all());

        $employee = User::create($request->all());
        $employee->confirmed = 1;
        if ($request->image) {
            if ($request->hasFile('image')) {

                $image = $request->file('image');
                //storing image name in a variable
                $image_name = time() . '.' . $image->getClientOriginalExtension();

                $destinationPath = public_path('/uploads/employee');
                if ($image->move($destinationPath, $image_name)) {
                    $employee->avatar_location = $image_name;
                }
            } else {
                $employee->avatar_location = $request->pictures;
            }
        }
        $employee->active = isset($request->active) ? 1 : 0;
        $employee->employee_type = 'internal';
        $save_id =  $employee->save();
        $employee->assignRole('student');

        if (isset($request->emp_id)) {
            DB::table('users')->where('id', $employee->id)->update(['emp_id' => $request->emp_id]);
        }



        $data = [
            'user_id' => $employee->id,
            'department' => $request->department,
            'position' => $request->position
        ];
        $max = EmployeeProfile::create($data);
        $max->position = $request->position;
        $max->save();

        try {
            $user_fav_lang = $employee->fav_lang;
            $username = $employee->full_name;

            if ($user_fav_lang == 'arabic') {
                $username = $employee->arabic_full_name ?? $employee->full_name;
            }

            $variables = [
                '{User_Name}' => $username,
                '{Academy_Website_Link}' => url('/' . '?openModal'),
                '{User_Email}' => $employee->email,
                '{Password}' => $request->password,
            ];

            $email_template = CustomHelper::emailTemplates('user_added', $user_fav_lang, $variables);

            $details = [
                'to_email' => $employee->email,
                'subject' => $email_template['subject'],
                'html' => view('emails.default_email_template', [
                    'user' =>  $employee,
                    'content' => $email_template
                ])->render(),
            ];


            dispatch(new SendEmailJob($details));
        } catch (Exception $e) {
            return response()->json(['status' => 'success', 'clientmsg' => 'Added successfully Mail Not Send']);
        }

        // if($save_id == true){
        //     dd('ki');
        //     return redirect()->route('admin.employee.index')->withFlashSuccess('Activation Mail send successfully');
        // }

        return response()->json(['status' => 'success', 'clientmsg' => 'Added successfully']);
        // return redirect()->route('admin.employee.index')->withFlashSuccess(trans('alerts.backend.general.created'));
        // return redirect()->route('admin.assessment_accounts.assignments')->withFlashSuccess(trans('Attach assessment here'));
    }


    /**
     * Show the form for editing Category.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $teacher = User::findOrFail($id);
        $countries = DB::table('master_countries')->get();
        if ($teacher->employee_type == 'external') {
            return view('backend.employee.edit_external_employee', compact('teacher', 'countries'));
        } else {
            $departments = Department::all();
            $positions = Position::all();
            $new_positions = EmployeeProfile::where('user_id', $id)->first();
            return view('backend.employee.edit', compact('teacher', 'departments', 'positions', 'new_positions'));
        }
    }


    public function update(UpdateEmployeeRequest $request, $id)
    {
        $teacher = User::findOrFail($id);
        if ($teacher->employee_type == 'external') {
            $teacher->update($request->except('email', 'password'));
            if ($request->has('image')) {
                if ($request->hasFile('image')) {

                    $image = $request->file('image');
                    $image_name = time() . '.' . $image->getClientOriginalExtension();

                    $destinationPath = public_path('/uploads/employee');
                    if ($image->move($destinationPath, $image_name)) {
                        $teacher->avatar_location = $image_name;
                    }
                } else {
                    //$employee->avatar_location=$request->pictures;
                }
            }
            $teacher->id_number = $request->id_number;
            $teacher->classfi_number = $request->class_number;
            $teacher->nationality = $request->nationality;
            $teacher->phone = $request->mobile_number;
            $teacher->dob = $request->dob;
            $teacher->active = isset($request->active) ? 1 : 0;
            $teacher->save();
        } else {
            $teacher->update($request->except('email'));
            if ($request->has('image')) {
                if ($request->hasFile('image')) {

                    $image = $request->file('image');
                    //dd($image);
                    //storing image name in a variable
                    $image_name = time() . '.' . $image->getClientOriginalExtension();

                    $destinationPath = public_path('/uploads/employee');
                    if ($image->move($destinationPath, $image_name)) {
                        $teacher->avatar_location = $image_name;
                    }
                } else {
                    //$employee->avatar_location=$request->pictures;
                }
            }
            $teacher->active = isset($request->active) ? 1 : 0;
            $teacher->save();

            $data = [
                'department' => $request->department,
                'position' => $request->position
            ];
            $data_exits = DB::table('employee_profiles')->where('user_id', $id)->first();
            if ($data_exits) {
                DB::table('employee_profiles')->where('user_id', $id)->update($data);
            } else {
                $data = [
                    'user_id' => $id,
                    'department' => $request->department,
                    'position' => $request->position
                ];
                DB::table('employee_profiles')->insert($data);
            }
        }


        if (isset($request->emp_id)) {
            DB::table('users')->where('id', $id)->update(['emp_id' => $request->emp_id]);
        }

        return redirect()->route('admin.employee.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }


    /**
     * Display Category.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $teacher = User::where('id', $id)->first();
        //dd($teacher);

        return view('backend.employee.show', compact('teacher'));
    }


    /**
     * Remove Category from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $teacher = User::findOrFail($id);
        //dd($teacher->courses->count());
        if ($teacher->courses->count() > 0) {
            return redirect()->route('admin.employee.index')->withFlashDanger(trans('alerts.backend.general.teacher_delete_warning'));
        } else {
            $teacher->delete();
        }

        return redirect()->route('admin.employee.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Category at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if ($request->input('ids')) {
            $entries = User::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }


    /**
     * Restore Category from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $teacher = User::onlyTrashed()->findOrFail($id);
        $teacher->restore();

        return redirect()->route('admin.employee.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Category from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        $teacher = User::onlyTrashed()->findOrFail($id);
        $teacher->teacherProfile->delete();
        $teacher->forceDelete();

        return redirect()->route('admin.employee.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }


    /**
     * Update teacher status
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     **/
    public function updateStatus()
    {
       
        $teacher = User::find(request('id'));
        $teacher->active = $teacher->active == 1 ? 0 : 1;
        $teacher->save();
        return redirect()->route('admin.assessment_accounts.assignments')->withFlashSuccess(trans('Mail for deactive send successfully'));
    }


    public function enrolled_student($course_id)
    {
        //dd($course_id);
        // $subscribe_courses = auth()->user()->subscribeCourses();
        return view('backend.employee.enrolled_employee', ['course_id' => $course_id]);
    }

    public function all_enrolled_student($course_id)
    {
        //dd($course_id);
        // $subscribe_courses = auth()->user()->subscribeCourses();
        return view('backend.employee.report', ['course_id' => $course_id]);
    }

    public function internal_reports($course_id = null)
    {

        // $reports = DB::table('reports')->join('users','users.id','reports.user_id')
        //                 ->join('department','department.id','reports.departments')
        //                 ->select('reports.*','users.first_name as username','department.title as department')
        //                 ->orderBy('reports.id','DESC')
        //                 ->get();
        // dd($reports1);
        return view('backend.employee.internal_report', ['course_id' => $course_id]);
    }


    public function enrolled_get_data(Request $request, $course_id, $show_deleted = 0, $search_type = null)
    {
        //dd($show_deleted);
        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $teachers = "";

        //dd($request->all());
        $search_type = $request->search_type ? $request->search_type : null;

        $teachers = SubscribeCourse::with('student');

        if (!empty($search_type)) {
            $teachers = $teachers->whereHas('student', function ($teachers) use ($search_type) {
                $teachers->where('employee_type', $search_type);
            });
        }


        $teachers->where('course_id', $course_id)->groupBy('user_id')
            ->orderBy('created_at', 'desc');




        $teachers = $teachers->get();
        //dd($teachers);

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
                    /*
                    $view = view('backend.datatable.action-view')
                        ->with(['route' => route('admin.employee.course_detail', [$course_id,$q->id])])->render();
                    */
                }

                if ($has_edit) {

                    $edit =  view('backend.datatable.action-edit')
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
            ->addColumn('email', function ($q) {

                return @$q->student->email ?? null;
            })
            ->addColumn('trainee_type', function ($q) {

                return @$q->student->employee_type ?? null;
            })
            ->addColumn('status', function ($q) {

                return ($q->status == 1) ? '<span class="pill-publish">Enabled</span>' : '<span class="pill-unpublish">Disabled</span>';
            })
            ->addColumn('course_completed', function ($q) use ($course_id, $request) {

                return CustomHelper::is_course_completed_status($q->user_id, $course_id);
            })
            ->addColumn('feedback', function ($q)  use ($course_id, $request) {

                return CustomHelper::is_user_course_has_feedback($q->user_id, $course_id) ?  '<a class="btn btn-info mb-1" href="' . route('admin.employee.course_detail', [$course_id, $q->user_id]) . '">Veiw FeedBack</a>'  : '--';
            })
            ->addColumn('issue_certificate', function ($q) use ($course_id, $request) {

                $has_certificate = CustomHelper::is_user_course_has_issed_certificate($q->user_id, $course_id);
                if ($has_certificate) {
                    $view_certificate_actions = '<a target="_blank" class="badge badge-success" href="' . asset('storage/certificates/' . $has_certificate->certificate_url) . '">View Certificate</a>';
                } else {
                    $is_completed = CustomHelper::is_course_completed($q->user_id, $course_id);
                    if ($is_completed) {
                        $view_certificate_actions = '<a class="badge badge-info" href="' . route('certificates.generate', [$course_id, $q->user_id]) . '">Issue Certificate</a>';
                    } else {
                        $view_certificate_actions = '--';
                    }
                }
                return $view_certificate_actions;
            })
            ->addColumn('track_employee', function ($q) use ($course_id, $request) {

                $is_course_started = CustomHelper::is_course_completed($q->user_id, $course_id);
                if ($is_course_started) {
                    return '<a target="_blank" class="badge badge-info" href="' . route('admin.employee.course_detail', [$course_id, $q->user_id]) . '"><span class="badge badge-info">Track Progress</span></a>';
                } else {
                    return '--';
                }
            })
            ->addColumn('percentage', function ($q) use ($course_id, $request) {

                $is_course_started = CustomHelper::is_course_completed($q->user_id, $course_id);
                if ($is_course_started) {
                    return '100%';
                } else {
                    return '--';
                }
            })
            ->addColumn('enrolled_date', function ($q) {
                return ($q->created_at) ? $q->created_at : '-';
            })
            ->rawColumns(['actions', 'feedback', 'issue_certificate', 'course_completed', 'email', 'trainee_type', 'image', 'status', 'track_employee', 'enrolled_date'])
            ->make();
    }

    public function generateCertificate($course_id, $user_id)
    {
        //dd($course_id);
        $course = Course::where('id', '=', $course_id);

        $course = $course->first();

        $user = User::find($user_id);
        //dd($course);

        if (($course != null)) {
            //dd($course);
            $certificate = Certificate::firstOrCreate([
                'user_id' => $user_id,
                'course_id' => $course_id
            ]);

            $data = [
                'name' => $user->name,
                'course_name' => $course->title,
                'date' => Carbon::now()->format('d M, Y'),
            ];
            $certificate_name = 'Certificate-' . $course->id . '-' . $user->id . '.pdf';
            $certificate->name = $user->name;
            $certificate->url = $certificate_name;
            $certificate->save();
            if ($certificate->id) {
                // $html = view('certificate.index', ['data'=> $data]);

                $pdf = \PDF::loadView('certificate.index', compact('data'))->setPaper('A3', 'portrait');
                $pdf->save(public_path('storage/certificates/' . $certificate_name));
                // return true;

                UserCourseDetail::where('course_id', $course->id)->where('user_id', $user->id)->update(
                    [
                        'issue_certificate' => 'yes',
                        'certificate_url' => $certificate_name
                    ]
                );
            }



            return back()->withFlashSuccess(trans('alerts.frontend.course.certificate_issued'));
        }
    }


    public function import(Request $request)
    {

        $IsSaved = false;

        if (request()->hasFile('file')) {

            $maximum_execution_time = Config::get('constants.maximum_execution_time');
            set_time_limit($maximum_execution_time);

            $ExcelData = Excel::toArray(new UsersImport, request()->file('file'));
            if (!empty($ExcelData)) {
                $ExtractedDataFromExcel = $ExcelData[0];

                if (!empty($ExtractedDataFromExcel)) {
                    $count = 0;

                    $TotalData = count($ExtractedDataFromExcel) - 0;
                    // echo '<pre>';    print_r($ExtractedDataFromExcel);die;
                    foreach ($ExtractedDataFromExcel as $ExcelKey => $ExcelValue) {

                        if ($count == 0) {
                            $count++;
                            continue;
                        }
                        $count++;
                        $IsDataSuccessfullyInserted = false;
                        $exist_email = User::where('email', trim($ExcelValue[2]))->first();
                        if (empty($exist_email)) {
                            if ($ExcelValue[2] != null) {
                                $RetailerPlanId = 0;
                                $RetailerPlan = new User();
                                $RetailerPlan->emp_id = trim($ExcelValue[0]);
                                $RetailerPlan->first_name = trim($ExcelValue[1]);
                                $RetailerPlan->last_name = trim($ExcelValue[2]);
                                $RetailerPlan->email = trim($ExcelValue[3]);
                                $RetailerPlan->work_id = trim($ExcelValue[6]);
                                $RetailerPlan->password = Hash::make($ExcelValue[4]);
                                //echo '<pre>';    print_r($ExcelValue);die;
                                $RetailerPlan->gender = trim($ExcelValue[7]);
                                $RetailerPlan->confirmed = 1;
                                $RetailerPlan->employee_type = 'internal';
                                $RetailerPlan->assignRole('student');
                                if ($RetailerPlan->save()) {
                                    $RetailerPlanId = $RetailerPlan->id;


                                    $mail = new PHPMailer(true);     // Passing `true` enables exceptions
                                    try {
                                        $mail->SMTPDebug = 0;
                                        $mail->isSMTP();
                                        $mail->Host = env('MAIL_HOST');             //  smtp host
                                        $mail->SMTPAuth = true;
                                        $mail->Username = env('MAIL_USERNAME');  //  sender username
                                        $mail->Password = env('MAIL_PASSWORD');       // sender password
                                        $mail->SMTPSecure = 'tls';                  // encryption - ssl/tls
                                        $mail->Port = 587;                          // port - 587/465
                                        $mail->setFrom(env('MAIL_USERNAME'), env('APP_NAME'));
                                        $mail->addAddress($ExcelValue[3]);
                                        $mail->isHTML(true);                // Set email content format to HTML
                                        $mail->Subject = "New User Registered " . env('APP_NAME');
                                        $mail->Body    = "# Hello $ExcelValue[1]<br>

In our system new user registered, User details are below<br>

Name * $ExcelValue[1] * <br>
Email * $ExcelValue[3] * <br>
Password * $ExcelValue[4] *

<br>
Thanks,<br>" . env('APP_NAME');
                                        $mail->send();
                                    } catch (Exception $e) {
                                        //return response()->json([ 'status'=>'success' , 'clientmsg' => 'Added successfully Mail Not Send' ]);
                                    }
                                }
                                if ($RetailerPlanId > 0) {

                                    $RetailerPlanDetail = new EmployeeProfile();
                                    $RetailerPlanDetail->user_id = $RetailerPlanId;
                                    if ($ExcelValue[5]) {
                                        $RetailerPlanDetail->department = trim($ExcelValue[5]);
                                    }

                                    $RetailerPlanDetail->position = trim($ExcelValue[6]);

                                    if ($RetailerPlanDetail->save()) {
                                        $IsDataSuccessfullyInserted = true;
                                        $IsSaved = true;
                                    }

                                    $exist_slug = Department::where('slug', str_slug(trim($ExcelValue[5])))->first();
                                    if ($exist_slug) {
                                        // return redirect()->route('admin.employee.index')->withFlashDanger('Department title is already exist');
                                        $RetailerPlanDetail->department = $exist_slug->id;
                                        $RetailerPlanDetail->save();
                                        $IsDataSuccessfullyInserted = true;
                                    } else {
                                        $dep = new Department();
                                        $dep->title = trim($ExcelValue[5]);
                                        $dep->slug = str_slug(trim($ExcelValue[5]));
                                        $message = trim($ExcelValue[1]);
                                        $dom = new \DOMDocument();
                                        $dom->loadHtml(mb_convert_encoding($message,  'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                                        $dep->content = $dom->saveHTML();
                                        $dep->user_id = auth()->user()->id;
                                        $dep->published = 1;
                                        $dep->sidebar = 1;
                                        if ($dep->save()) {
                                            if (empty($ExcelValue[4])) {
                                                $RetailerPlanDetail->department = $dep->id;
                                                $RetailerPlanDetail->save();
                                            }
                                            // $RetailerPlanId = $RetailerPlan->id;
                                            $IsDataSuccessfullyInserted = true;
                                        }
                                    }
                                    // $dep = new Department();

                                }
                                if ($IsDataSuccessfullyInserted) {
                                    $TotalData++;
                                }
                            }
                        } else if ($exist_email) {
                            return redirect()->route('admin.employee.index')->withFlashDanger('Email is already exist');
                        }
                    }
                }
            }
        }
        if ($IsSaved) {
            return redirect()->route('admin.employee.index')->withFlashSuccess(trans('alerts.backend.general.created'));
        }
        return redirect()->route('admin.employee.index')->withFlashDanger('Something went wrong');
    }

    public function external_employee_create()
    {
        $countries = DB::table('master_countries')->get();
        return view('backend.employee.create_external_employee', compact('countries'));
    }

    public function external_employee_store(Request $request)
    {

        $employee = User::create($request->all());
        $employee->confirmed = 1;
        if ($request->image) {
            if ($request->hasFile('image')) {

                $image = $request->file('image');
                $image_name = time() . '.' . $image->getClientOriginalExtension();

                $destinationPath = public_path('/uploads/employee');
                if ($image->move($destinationPath, $image_name)) {
                    $employee->avatar_location = $image_name;
                }
            } else {
                $employee->avatar_location = $request->pictures;
            }
        }
        $employee->id_number = $request->id_number;
        $employee->classfi_number = $request->class_number;
        $employee->nationality = $request->nationality;
        $employee->phone = $request->mobile_number;
        $employee->dob = $request->dob;
        $employee->active = isset($request->active) ? 1 : 0;
        $employee->employee_type = 'external';
        $employee->save();
        $employee->assignRole('student');

        //require base_path("vendor/autoload.php");

        $mail = new PHPMailer(true);     // Passing `true` enables exceptions

        try {

            // Email server settings
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST');             //  smtp host
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');  //  sender username
            $mail->Password = env('MAIL_PASSWORD');       // sender password
            $mail->SMTPSecure = 'tls';                  // encryption - ssl/tls
            $mail->Port = 587;                          // port - 587/465

            $mail->setFrom(env('MAIL_USERNAME'), env('APP_NAME'));
            $mail->addAddress($request->email);
            $mail->isHTML(true);                // Set email content format to HTML
            $mail->Subject = "New User Registered " . env('APP_NAME');
            $mail->Body    = "# Hello $request->first_name<br>

                In our system new user registered, User details are below<br>

                Name * $request->first_name * <br>
                Email * $request->email * <br>
                Password * $request->password *

                <br>
                Thanks,<br>" . env('APP_NAME');
            $mail->send();
            // $mail->AltBody = plain text version of email body;



        } catch (Exception $e) {
            return response()->json(['status' => 'success', 'clientmsg' => 'Added successfully Mail Not Send']);
        }

        return redirect()->route('admin.employee.external_index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }

    // public function internal_reports(){

    //     $reports = DB::table('reports')->join('users','users.id','reports.user_id')
    //                     ->join('department','department.id','reports.departments')
    //                     ->select('reports.*','users.first_name as username','department.title as department')
    //                     ->orderBy('reports.id','DESC')
    //                     ->get();
    //     // dd($reports1);
    //     return view('backend.employee.internal_report',compact('reports'));
    // }

    public function reports_create_internal()
    {
        $departments = Department::all();
        $teachers =  \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('role_id', 3)->where('employee_type', 'external')->orWhere('employee_type', 'internal');
        })->get()->pluck('name', 'id');
        return view('backend.employee.create_internal_report', compact('departments', 'teachers'));
    }

    public function reports_store_internal(Request $request)
    {

        $reports = new Reports();
        $reports->user_id = $request->teachers;
        $reports->departments = $request->department;
        $reports->exam_score = $request->score;
        $reports->status = $request->status;
        $reports->save();
        return redirect()->route('admin.employee.internal_reports')->withFlashSuccess(trans('Internal Reports Added succefully'));
    }



    public function enrolled_get_data_internal(Request $request, $course_id, $show_deleted = 0, $search_type = null)
    {
        // dd($course_id);
        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $teachers = "";

        //dd($request->all());
        $search_type = $request->search_type ? $request->search_type : null;

        $teachers = SubscribeCourse::with('student');

        if (!empty($search_type)) {
            $teachers = $teachers->whereHas('student', function ($teachers) use ($search_type) {
                $teachers->where('employee_type', $search_type);
            });
        }


        // dd(auth()->user()->id);
        $teachers =  \App\Models\Auth\User::whereHas('roles', function ($q) {
            $user_id = auth()->user()->id;
            $q->where('role_id', 3)->where('employee_type', 'internal')->groupBy('id')
                ->orderBy('created_at', 'desc');
        })->get();

        // $teachers->where('course_id',$course_id)->groupBy('user_id')
        // ->orderBy('created_at','desc');





        // $teachers = $teachers->get();

        //dd($teachers);

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
                    /*
                    $view = view('backend.datatable.action-view')
                        ->with(['route' => route('admin.employee.course_detail', [$course_id,$q->id])])->render();
                    */
                }

                if ($has_edit) {

                    $edit =  view('backend.datatable.action-edit')
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
            ->addColumn('email', function ($q) {
                // dd($q->email);
                return $q->email;
            })
            ->addColumn('cousre_name', function ($q) {
                // dd($q);
                return $q->title;
            })
            ->addColumn('status', function ($q) {
                //  dd($q);
                return ($q->active == 1) ? '<span class="pill-published">Enabled</span>' : '<span class="pill-unpublished">Disabled</span>';
            })
            ->addColumn('course_completed', function ($q) use ($course_id, $request) {

                return CustomHelper::is_course_completed_status($q->id, $course_id);
            })
            ->addColumn('feedback', function ($q)  use ($course_id, $request) {

                return CustomHelper::is_user_course_has_feedback($q->id, $course_id) ?  '<a class="btn btn-info mb-1" href="' . route('admin.employee.course_detail', [$course_id, $q->id]) . '">Veiw FeedBack</a>'  : '--';
            })
            ->addColumn('issue_certificate', function ($q) use ($course_id, $request) {

                $has_certificate = CustomHelper::is_user_course_has_issed_certificate($q->id, $course_id);
                if ($has_certificate) {
                    $view_certificate_actions = '<a target="_blank" class="badge badge-success" href="' . asset('storage/certificates/' . $has_certificate->certificate_url) . '">View Certificate</a>';
                } else {
                    $is_completed = CustomHelper::is_course_completed($q->id, $course_id);
                    if ($is_completed) {
                        $view_certificate_actions = '<a class="badge badge-info" href="' . route('certificates.generate', [$course_id, $q->id]) . '">Issue Certificate</a>';
                    } else {
                        $view_certificate_actions = '--';
                    }
                }
                return $view_certificate_actions;
            })
            ->addColumn('track_employee', function ($q) use ($course_id, $request) {

                $is_course_started = CustomHelper::is_course_completed($q->id, $course_id);
                if ($is_course_started) {
                    return '<a target="_blank" class="badge badge-info" href="' . route('admin.employee.course_detail', [$course_id, $q->id]) . '"><span class="badge badge-info">Track Progress</span></a>';
                } else {
                    return '--';
                }
            })
            ->addColumn('percentage', function ($q) use ($course_id, $request) {

                $is_course_started = CustomHelper::is_course_completed($q->id, $course_id);
                if ($is_course_started) {
                    return '100%';
                } else {
                    return '--';
                }
            })
            ->addColumn('enrolled_date', function ($q) {
                return ($q->created_at) ? $q->created_at : '-';
            })
            ->rawColumns(['actions', 'feedback', 'issue_certificate', 'course_completed', 'email', 'cousre_name', 'image', 'status', 'track_employee', 'enrolled_date'])
            ->make();
    }

    public function reset_pass(Request $request, $id)
    {
        $user = User::where('id', $id)->first();

        if ($user) {
            try {

                $token = Str::random(32);
                PasswordReset::updateOrCreate([
                    'email' => $user->email
                ], [
                    'token' => $token
                ]);
                $password_reset_link = url("change-password/$token");

                $user_fav_lang = $user->fav_lang;
                $username = $user->full_name;

                if ($user_fav_lang == 'arabic') {
                    $username = $user->arabic_full_name ?? $user->full_name;
                }

                $variables = [
                    '{User_Name}' => $username,
                    '{Link}' => $password_reset_link,
                ];

                $email_template = CustomHelper::emailTemplates('reset_password', $user_fav_lang, $variables);

                $details = [
                    'to_email' => $user->email,
                    'subject' => $email_template['subject'],
                    'html' => view('emails.default_email_template', [
                        'user' =>  $user,
                        'content' => $email_template
                    ])->render(),
                ];

                dispatch(new SendEmailJob($details));
            } catch (Exception $e) {
                return response()->json(['status' => 'success', 'clientmsg' => 'Added successfully Mail Not Send']);
            }
        } else {
            return response()->json(['status' => 'success', 'clientmsg' => 'Added successfully Mail Not Send']);
        }

        // $teacher = User::where('id',$id)->first();
        // $teacher->password = Hash::make($request->password);
        // $teacher->updated_at = date('Y-m-d H:i:s');
        // $teacher->save();
        return redirect()->route('admin.employee.index')->withFlashSuccess(trans('Password reset link send successfully'));
    }

    public function internal_trainee_info()
    {
        return view('backend.employee.internal_info');
    }

    public function external_trainee_info()
    {
        return view('backend.employee.external_info');
    }

    public function get_external_trainee_info(Request $request)
    {

        $teachers = "";


        if (request('show_deleted') == 1) {
            $teachers = User::query()->role('student')->where('employee_type', 'external')->onlyTrashed()->orderBy('created_at', 'desc');
        } else {
            $teachers = User::query()->role('student')->where('employee_type', 'external')->orderBy('created_at', 'desc');
        }



        return DataTables::of($teachers)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($request) {
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.employee', 'label' => 'id', 'value' => $q->id]);
                }
                //$view .= '<a class="btn btn-warning mb-1" href="' . route('admin.courses.index', ['teacher_id' => $q->id]) . '">' . trans('labels.backend.courses.title') . '</a>';

            })
            // ->addColumn('image', function($q) {
            //     return '<img src="'.$q->avtar_type.'" width="95px"/>';
            //  })
            ->addColumn('nationality', function ($q) {
                $deaprt = $q->getcountry();
                return $deaprt;
            })
            ->addColumn('status', function ($q) {
                // dd($q);
                $checked = $q->active == 1 ? 'checked' : '';
                $html = '<div class="custom-control custom-switch">
                <input type="checkbox" 
                       class="custom-control-input status-toggle" 
                       id="switch' . $q->id . '" 
                       data-id="' . $q->id . '" 
                       value="1" ' . $checked . '>
                <label class="custom-control-label" for="switch' . $q->id . '"></label>
            </div>
                        ';
                return $html;
                // return ($q->active == 1) ? "Enabled" : "Disabled";
            })
            ->rawColumns(['actions', 'nationality', 'position', 'image', 'status'])
            ->make();
    }

    // public function internal_attendence_report()
    // {
    //     $data = [];
    //     // $data['val'] = SubscribeCourse::join('courses', 'courses.id', 'subscribe_courses.course_id')
    //     //     ->join('users', 'users.id', 'subscribe_courses.user_id')
    //     //     ->leftJoin('video_progresses', 'video_progresses.user_id', 'users.id')
    //     //     ->leftJoin('course_user', 'course_user.course_id', 'courses.id')
    //     //     ->leftJoin('assignments', 'assignments.course_id', 'courses.id')
    //     //     ->join('employee_profiles', 'employee_profiles.user_id', 'users.id')
    //     //     //->groupBy('users.id')
    //     //     ->select('course_user.user_id as course_user_id', 'video_progresses.progress_per', 'courses.id as this_course_id', 'users.*', 'courses.*', 'users.id as this_users_id', 'subscribe_courses.*', 'subscribe_courses.created_at as sub_created_at', 'employee_profiles.department', 'employee_profiles.position', 'assignments.id as assignment_id')
    //     //     ->where('user_id','4319')
    //     //     ->where('employee_type', 'internal')
    //     //     ->get();
    //     // echo '<pre>'; print_r($data['val']);die;     
    //     $subscribeCourse = SubscribeCourse::orderBy('id', 'Desc')->get();

    //     $data['val'] = [];


    //     $i = 0;
    //     foreach ($subscribeCourse as $value) {

    //         $value['user_detail'] = DB::table('users')->where('id', $value->user_id)->first();

    //         $courses = DB::table('courses')->where('id', $value->course_id)->first();

    //         $value['courses'] = $courses;

    //         $course_user = DB::table('course_user')->where('course_id', $value->course_id)->first();
    //         $value['course_user'] = $course_user;
    //         $value->username = '';
    //         if (!empty($course_user)) {
    //             $username = DB::table('users')->where('id', $course_user->user_id)->first();
    //             $value->username = $username->first_name . ' ' . $username->last_name;
    //         }

    //         $video_progresses = DB::table('video_progresses')->where('user_id', $value->user_id)->first();

    //         $value['video_progresses'] = $video_progresses;
    //         if (!empty($video_progresses)) {
    //             $value->progress_per = $video_progresses->progress_per;

    //             $value->progress_per = CustomHelper::progress($value->course_id, $value->user_id);

    //             if ($value->progress_per > 0 && $value->progress_per < 70) {
    //                 $value->progress_status = 'In progress';
    //             } else if ($value->progress_per >= 70) {
    //                 $value->progress_status = 'Completed';
    //             } else {
    //                 $value->progress_status = 'Not started';
    //             }
    //         } else {
    //             $value->progress_status = 'Not started';
    //             $value->progress_per = '0';
    //         }
    //         $value['assignments'] = DB::table('assignments')->where('course_id', $value->course_id)->first();
    //         $value['employee_profiles'] = DB::table('employee_profiles')->where('user_id', $value->user_id)->first();

    //         //$course_status= CustomHelper::progress($value->course_id);
    //         //echo '<pre>';print_r($course_status);die;


    //         if ($value->sub_created_at != null || $value->sub_created_at) {
    //             $value->assign_date = '-';
    //         }



    //         //             $i++;
    //     }


    //     //echo '<pre>'; print_r($subscribeCourse);die;    
    //     return view('backend.employee.internal_attendace_report', compact('data', 'subscribeCourse'));
    // }

    public function exportInternalAttendenceReportAsCsv(Request $request)
    {
        ini_set('max_execution_time', 3000);
        $params = $request->all();
        $params['logged_user_id'] = auth()->user()->id;

        //return Excel::download(new InternalAttendanceReportExport($params), 'internal-attendance-report.csv');


        ExportInternalReportNotification::where('user_id', auth()->user()->id)->delete();
        //Make an entry for download export for internal report
        ExportInternalReportNotification::create(
            [
                'user_id' => auth()->user()->id,
                'status' => 0,
                'download_link' => null,
            ]
        );
        //dd($params);
        GenerateInternalAttendanceReport::dispatch($params);

        return response()->json([
            'message' => 'Your report is being generated and will be available soon.'
        ]);
    }


    public function checkExportDownloadReady()
    {
        $status = false;
        $er = ExportInternalReportNotification::query()
            ->where('user_id', auth()->user()->id)
            ->where('status', 1)
            ->first();
        $download_file = isset($er) ? asset($er->download_link) : null;
        if ($er) {
            $status = true;
        }


        return response()->json([
            'message' => 'Export is ready for download now.',
            'status' => $status,
            'download_file' => $download_file,
        ]);
    }


    public function exportTraineesAsCsv()
    {
        ini_set('max_execution_time', 3000);
        return Excel::download(new TraineesExport, 'trainees.csv');
    }

    public function internal_attendence_report__(Request $request)
    {
        
        //Artisan::call('generate-user-internal-report');

        if ($request->ajax()) {

            ExportInternalReportNotification::where('user_id', auth()->user()->id)->delete();

            $user_id = $request->user_id ?? null;
            $course_id = $request->course_id ?? null;
            $assign_from_date = $request->from ?? null;
            $assign_to_date = $request->to ?? null;

            //dd($request->all());

            $subscribeCourse = SubscribeCourse::with('user', 'student', 'course')
                                    ->whereHas('user', function ($query) use ($user_id) {
                                        $query->where('employee_type', 'internal');
                                        if (!empty($user_id)) {
                                            $query->where('id', $user_id);
                                        }
                                    })
                                    ->when(!empty($assign_from_date) && !empty($assign_to_date), function ($q) use ($assign_from_date, $assign_to_date) {
                                        $q->whereBetween('assign_date', [$assign_from_date, $assign_to_date]);
                                    })
                                    ->when(!empty($assign_from_date) && empty($assign_to_date), function ($q) use ($assign_from_date) {
                                        $q->whereDate('assign_date', '>=', $assign_from_date);
                                    })
                                    ->whereHas('course')
                                    ->when(!empty($course_id), function ($q) use ($course_id) {
                                        $q->where('course_id', $course_id);
                                    })
                                    ->orderBy('id', 'desc');
                                    //->toSql();

            //dd($subscribeCourse);

                                 
            return DataTables::of($subscribeCourse)
                ->setRowClass(function ($row) {
                    //return @$row->student->active == '0' ? 'table-danger' : '';
                })
                ->addColumn('user_status', function ($row) {
                    if($row->student) {
                        return @$row->student->active == 0 ? "InActive" : "Active";
                    } else {
                        return '-';
                    }
                    
                })
                ->addColumn('emp_id', function ($row) {
                    return  $row->student ? @$row->student->emp_id : '-';
                })
                ->addColumn('emp_type', function ($row) {
                    return $row->student ? @$row->student->employee_type : '-';
                })
                ->addColumn('emp_name', function ($row) {
                    return $row->student ? @$row->student->first_name . " " . @$row->student->last_name : '-';
                })
                ->addColumn('emp_email', function ($row) {
                    return  $row->student ? @$row->student->email : '-';
                })
                ->addColumn('department', function ($row) {
                    return @$row->employeeProfile->department_details->title;
                })
                ->addColumn('emp_postition', function ($row) {
                    return @$row->employeeProfile->position;
                })
                ->addColumn('enroll_type', function ($row) {
                    return 'Assigned';
                })
                ->addColumn('course_category', function ($row) {
                    return @$row->course->category->name;
                })
                ->addColumn('course', function ($row) {
                    return @$row->course->title;
                })
                ->addColumn('course_code', function ($row) {
                    return @$row->course->course_code;
                })
                ->addColumn('progress_per', function ($row) {
                    return @$row->assignment_progress ?  @$row->assignment_progress . '%' : '0%';//CustomHelper::progress($row->course_id, $row->user_id) . '%';
                })
                ->addColumn('assignment_score', function ($row) {
                    return $row->has_assesment > 0 ? $row->assignmentScoreWithHtml(@$row->student->id) : '-';
                })
                ->addColumn('progress_status', function ($row) {
                    $progress = @$row->assignment_progress ?? 0;
                    if ($progress > 0 && $progress < 70) {
                        return 'In progress';
                    } elseif ($progress >= 70) {
                        return 'Completed';
                    }
                    return 'Not started';
                })
                ->addColumn('assignment_status', function ($row) {
                    return  $row->has_assesment > 0 ? @$row->assignment_status : '-';
                })
                ->addColumn('trainer_name', function ($row) {
                    return  @$row->course_trainer_name;
                })
                ->addColumn('assign_date', function ($row) {
                   
                    //return @$row->courseAssignment()->assign_date != null ? Carbon::parse(@$row->courseAssignment()->assign_date)->format('d/m/Y') : '';
                    return $row->assign_date != null && $row->assign_date != '0000-00-00' ? Carbon::parse(@$row->assign_date)->format('d-F-Y') : '';
                    
                })
                ->addColumn('due_date', function ($row) {
                    //return @$row->courseAssignment()->due_date != null ? Carbon::parse(@$row->courseAssignment()->due_date)->format('d/m/Y') : '';
                    return $row->due_date != null && $row->due_date != '0000-00-00' ? Carbon::parse(@$row->due_date)->format('d-F-Y') : '';
                })
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $search = $request->input('search.value');
                        $query->whereHas('student', function ($query) use ($search) {
                            $query->where('emp_id', 'like', "%{$search}%")
                                ->where('employee_type', 'like', "%{$search}%")
                                ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                            })
                            ->orWhereHas('employeeProfile', function ($query) use ($search) {
                                $query->where('position', 'like', "%{$search}%");
                            })
                            ->orWhereHas('employeeProfile.department_details', function ($query) use ($search) {
                                $query->where('title', 'like', "%{$search}%");
                            })
                            ->orWhereHas('course', function ($query) use ($search) {
                                $query->where('title', 'like', "%{$search}%");
                                $query->where('course_code', 'like', "%{$search}%");
                            });
                    }
                })
                ->rawColumns(['assignment_score'])
                ->make(true);
        }

        $internal_users = User::query()
                                ->where('employee_type', 'internal')
                                //->where('active','1')
                                ->get();

        $published_courses = Course::query()
                                //->where('published', '1')
                                ->get();

        return view('backend.employee.internal_attendace_report',[
            'internal_users' => $internal_users,
            'published_courses' => $published_courses
        ]);
    }

    public function internal_attendence_report(Request $request)
    {

        //Artisan::call('generate-user-internal-report');

        if ($request->ajax()) {

            ExportInternalReportNotification::where('user_id', auth()->user()->id)->delete();

            $user_id = $request->user_id ?? null;
            $course_id = $request->course_id ?? null;
            $assign_from_date = $request->from ?? null;
            $assign_to_date = $request->to ?? null;
            $due_date = $request->due_date ?? null;
            $dept_id = $request->dept_id ?? null;

            if (empty($dept_id) && empty($user_id) && empty($course_id) && empty($assign_from_date) && empty($assign_to_date) && empty($due_date) ) {
                return DataTables::of(collect())->make(true); // return empty set
            }

            $subscribeCourse = SubscribeCourse::with('user', 'user.employee', 'student', 'course')
                ->whereHas('user', function ($query) use ($user_id, $dept_id) {
                    $query->where('employee_type', 'internal');
                    if (!empty($user_id)) {
                        $query->where('id', $user_id);
                    }
                    if (!empty($dept_id)) {
                        $query->whereHas('employee', function ($q) use ($dept_id) {
                            $q->where('department', $dept_id);
                        });
                    }
                })
                ->when(!empty($assign_from_date) && !empty($assign_to_date), function ($q) use ($assign_from_date, $assign_to_date) {
                    $q->whereBetween('assign_date', [$assign_from_date, $assign_to_date]);
                })
                ->when(!empty($assign_from_date) && empty($assign_to_date), function ($q) use ($assign_from_date) {
                    $q->whereDate('assign_date', '>=', $assign_from_date);
                })
                ->whereHas('course')
                ->when(!empty($course_id), function ($q) use ($course_id) {
                    $q->where('course_id', $course_id);
                })
                ->when(!empty($due_date), function ($q) use ($due_date) {
                    $q->whereDate('due_date', $due_date);
                })
                ->orderBy('id', 'desc');
                //->toSql();

            //dd($subscribeCourse);


            return DataTables::of($subscribeCourse)
                ->setRowClass(function ($row) {
                    //return @$row->student->active == '0' ? 'table-danger' : '';
                })
                ->addColumn('user_status', function ($row) {
                    if ($row->student) {
                        return @$row->student->active == 0 ? "InActive" : "Active";
                    } else {
                        return '-';
                    }
                })
                ->addColumn('emp_id', function ($row) {
                    return  $row->student ? @$row->student->emp_id : '-';
                })
                ->addColumn('emp_type', function ($row) {
                    return $row->student ? @$row->student->employee_type : '-';
                })
                ->addColumn('emp_name', function ($row) {
                    return $row->student ? @$row->student->first_name . " " . @$row->student->last_name : '-';
                })
                ->addColumn('emp_email', function ($row) {
                    return  $row->student ? @$row->student->email : '-';
                })
                ->addColumn('department', function ($row) {
                    return $row->employeeProfile ? @$row->employeeProfile->department_details->title : '-';
                })
                ->addColumn('emp_postition', function ($row) {
                    return $row->employeeProfile ? @$row->employeeProfile->position : '-';
                })
                ->addColumn('enroll_type', function ($row) {
                    return 'Assigned';
                })
                ->addColumn('course_category', function ($row) {
                    return $row->course ? @$row->course->category->name : '-';
                })
                ->addColumn('course', function ($row) {
                    return $row->course ? @$row->course->title : '-';
                })
                ->addColumn('course_code', function ($row) {
                    return $row->course ? @$row->course->course_code : '-';
                })
                ->addColumn('progress_per', function ($row) {

                    $progress = CustomHelper::progress($row->course_id, $row->user_id);
                    if ($progress > 0) {
                        return $progress . '%';
                    } else {
                        return '0%';
                    }
                    
                })
                ->addColumn('assignment_score', function ($row) {
                    if($row->has_assesment) {
                        return @$row->student->id ? @$row->assignmentScoreWithHtml(@$row->student->id) : '-';
                    } else {
                        return '-';
                    }
                })
                ->addColumn('progress_status', function ($row) {
                    $progress = CustomHelper::progress($row->course_id, $row->user_id);
                    if ($progress > 0 && $progress < 70) {
                        return 'In progress';
                    } elseif ($progress >= 70) {
                        return 'Completed';
                    }
                    return 'Not started';
                })
                ->addColumn('assignment_status', function ($row) {
                    if($row->has_assesment) {
                        //$progress = CustomHelper::progress($row->course_id, $row->user_id);
                        //dd($progress);
                        if($row->assesment_taken) {
                            $score = @$row->student->id ? @$row->assignmentRawScore(@$row->student->id) : 0;
                            if($score >= 70) {
                                return 'Passed';
                            } else {
                                return 'Failed';
                            }
                        } else {
                            return 'Not Started';
                        }
                    } else {
                        return 'Not Applied';
                    }
                     
                })
                ->addColumn('trainer_name', function ($row) {
                    $courseUser = DB::table('course_user')->where('course_id', $row->course_id)->first();
                    if ($courseUser) {
                        $user = DB::table('users')->where('id', $courseUser->user_id)->first();
                        return $user ? @$user->first_name . ' ' . @$user->last_name : '';
                    }
                    return '';
                })
                ->addColumn('assign_date', function ($row) {

                    //return @$row->courseAssignment()->assign_date != null ? Carbon::parse(@$row->courseAssignment()->assign_date)->format('d/m/Y') : '';
                    return $row->assign_date != null && $row->assign_date != '0000-00-00' ? Carbon::parse(@$row->assign_date)->format('d-F-Y') : '';
                })
                ->addColumn('due_date', function ($row) {
                    //return @$row->courseAssignment()->due_date != null ? Carbon::parse(@$row->courseAssignment()->due_date)->format('d/m/Y') : '';
                    return $row->due_date != null && $row->due_date != '0000-00-00' ? Carbon::parse(@$row->due_date)->format('d-F-Y') : '';
                })
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $search = $request->input('search.value');
                        $query->whereHas('student', function ($query) use ($search) {
                            $query->where('emp_id', 'like', "%{$search}%")
                                ->where('employee_type', 'like', "%{$search}%")
                                ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                            ->orWhereHas('employeeProfile', function ($query) use ($search) {
                                $query->where('position', 'like', "%{$search}%");
                            })
                            ->orWhereHas('employeeProfile.department_details', function ($query) use ($search) {
                                $query->where('title', 'like', "%{$search}%");
                            })
                            ->orWhereHas('course', function ($query) use ($search) {
                                $query->where('title', 'like', "%{$search}%");
                                $query->where('course_code', 'like', "%{$search}%");
                            });
                    }
                })
                ->rawColumns(['assignment_score'])
                ->make(true);
        }

        $internal_users = User::query()
            ->where('employee_type', 'internal')
            //->where('active','1')
            ->get();

        $published_courses = Course::query()
            //->where('published', '1')
            ->get();

        $published_department = Department::query()
                ->where('published', '1')
                ->get();

        return view('backend.employee.internal_attendace_report', [
            'internal_users' => $internal_users,
            'published_courses' => $published_courses,
            'published_department' => $published_department
        ]);
    }

    public function sync_report(Request $request)
    {
        Artisan::call('update-assesmentstatus-score-subscribecourses');

        return response()->json([
            'success' => true,
            'message' => 'Report synced successfully.'
        ]);
    }

    public function view_user_asssessement_answers($user_id, $course_id)
    {
        //dd("dd");
        $assessements = Test::where('course_id', $course_id)->get();
        //dd($assessements);

        $sc = SubscribeCourse::query()
            ->where('course_id', $course_id)
            ->where('user_id', $user_id)
            ->first();
        //$assessements[0]->test_active_questions();
        $completed_at = isset($sc) && isset($sc->completed_at) ? $sc->completed_at->format('Y-m-d H:i:s') : null;
        $is_completed = isset($sc) ? $sc->is_completed : 0;
        if ($assessements[0]) {
            $assessements[0]->test_active_questions($is_completed, $completed_at);
        }

        


        $marks = @$sc ? @$sc->assignmentScore($user_id) : 'N/A';
        //dd( $marks );
        return view('backend.employee.view_user_asssessement_answers', compact('assessements', 'marks', 'completed_at', 'is_completed'));
    }

    public function external_attendence_report()
    {
        $val = SubscribeCourse::join('courses', 'courses.id', 'subscribe_courses.course_id')
            ->join('users', 'users.id', 'subscribe_courses.user_id')
            ->leftJoin('video_progresses', 'video_progresses.user_id', 'users.id')
            ->leftJoin('course_user', 'course_user.course_id', 'courses.id')
            ->leftJoin('assignments', 'assignments.course_id', 'courses.id')
            //->leftJoin('assignments','assignments.course_id','courses.id')
            ->join('employee_profiles', 'employee_profiles.user_id', 'users.id')
            ->groupBy('users.id')
            ->select('course_user.user_id as course_user_id', 'video_progresses.progress_per', 'courses.id as this_course_id', 'users.*', 'courses.*', 'users.id as this_users_id', 'subscribe_courses.*', 'subscribe_courses.created_at as sub_created_at', 'employee_profiles.department', 'employee_profiles.position')
            ->where('employee_type', 'external')
            ->get();
        return view('backend.employee.external_attendace_report', compact('val'));
    }
}
