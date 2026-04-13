<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Models\Course;
use App\Models\CourseTimeline;
use App\Models\Lesson;
use Carbon\Carbon;
use App\Models\Media;
use App\Models\Test;
use App\Helpers\CustomHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLessonsRequest;
use App\Http\Requests\Admin\UpdateLessonsRequest;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Models\Category;
use Yajra\DataTables\Facades\DataTables;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\Backend\LessonNotification;
use App\Services\NotificationSettingsService;
use Illuminate\Support\Str;
use App\Models\LessonVideo;

class LessonsController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of Lesson.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!Gate::allows('lesson_access')) {
            return abort(401);
        }
        $courses = Course::pluck('title', 'id')->prepend('Please select', '');

        return view('backend.lessons.index', compact('courses'));
    }

    /**
     * Display a listing of Lessons via ajax DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $lessons = Lesson::with(['attendance_list', 'course'])
        ->where('live_lesson', '=', 0)
        ->whereIn('course_id', Course::pluck('id'));

        if ($request->show_deleted == 1) {
            if (!Gate::allows('lesson_delete')) {
                return abort(401);
            }
            $lessons = $lessons->onlyTrashed();
        }

        if ($request->course_id != "") {
            $lessons = $lessons->where('course_id', (int)$request->course_id);
        }

        $lessons = $lessons->orderBy('id', 'asc');




        if (auth()->user()->can('lesson_view')) {
            $has_view = true;
        }
        if (auth()->user()->can('lesson_edit')) {
            $has_edit = true;
        }
        if (auth()->user()->can('lesson_delete')) {
            $has_delete = true;
        }

        return DataTables::of($lessons)
            ->addIndexColumn()
            // ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
            //     $view = "";
            //     $edit = "";
            //     $delete = "";
            //     if ($request->show_deleted == 1) {
            //         return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.lessons', 'label' => 'id', 'value' => $q->id]);
            //     }
            //     if ($has_view) {
            //         $view = view('backend.datatable.action-view')
            //             ->with(['route' => route('admin.lessons.show', ['lesson' => $q->id])])->render();
            //     }
            //     if ($has_edit) {
            //         $edit = view('backend.datatable.action-edit')
            //             ->with(['route' => route('admin.lessons.edit', ['lesson' => $q->id])])
            //             ->render();
            //         $view .= $edit;
            //     }

            //     if ($has_delete) {
            //         $delete = view('backend.datatable.action-delete')
            //             ->with(['route' => route('admin.lessons.destroy', ['lesson' => $q->id])])
            //             ->render();
            //         $view .= $delete;
            //     }

            //     if (auth()->user()->can('test_view')) {
            //         if ($q->test != "") {
            //             $view .= '<a href="' . route('admin.tests.index', ['lesson_id' => $q->id]) . '" class="btn btn-success btn-block mb-1">' . trans('labels.backend.tests.title') . '</a>';
            //         }
            //     }

            //     return $view;
            // })
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
        if ($request->show_deleted == 1) {
        return view('backend.datatable.action-trashed')->with([
            'route_label' => 'admin.lessons',
            'label' => 'id',
            'value' => $q->id
        ]);
    }

    
      $actions = '<div class="action-pill">';

    if ($has_view) {
        $actions .= '<a class="" href="' . route('admin.lessons.show', ['lesson' => $q->id]) . '">
             <i class="fa fa-eye" aria-hidden="true"></i></a>';
    }

    if ($has_edit) {
        $actions .= '<a class="" href="' . route('admin.lessons.edit', ['lesson' => $q->id]) . '">
           <i class="fa fa-edit" aria-hidden="true"></i></a>';
    }

    if ($has_delete) {
        // $actions .= '
        //     <form method="POST" action="' . route('admin.lessons.destroy', $q->id) . '" class="" >
        //         ' . csrf_field() . method_field('DELETE') . '
        //         <a type="submit" class="" onclick="return confirm(\'Are you sure?\')">
        //              <i class="fa fa-trash" aria-hidden="true"></i>
        //         </a>
        //     </form>';
    }

    $actions .= '</div>';
    return $actions;
})
            ->editColumn('course', function ($q) {
    if ($q->course) {
        return '<a href="'.route('admin.courses.edit', $q->course->id).'">'
            . e($q->course->title) .
        '</a>';
    }
    return 'N/A';
})
            ->addColumn('attendance', function ($q) {
                $courseId = (int) ($q->course_id ?? optional($q->course)->id ?? 0);

                if ($courseId <= 0) {
                    return 0;
                }

                if (isset($q->attendance_list) && count($q->attendance_list)) {
                    return $q->attendance_list ? '<a href="' . route('attendance.attendance.list', [$courseId, $q->id]) . '">View All (' . count($q->attendance_list) . ')</a>' : 0;
                } else {
                    return 0;
                }
            })
            // ->addColumn('qr_code', function ($q) {
            //     return QrCode::size(80)->generate(route('attendance.attendance.lesson', [$q->course->id, $q->id]));
            // })
                ->addColumn('qr_code', function ($q) {
            $courseId = (int) ($q->course_id ?? optional($q->course)->id ?? 0);
            if ($courseId <= 0) {
            return 'N/A';
            }

    $modalId = 'qrModal_' . $q->id;

    // Use original logic to generate the QR code
            $qrCodeHtml = \QrCode::size(200)->generate(route('attendance.attendance.lesson', [$courseId, $q->id]));

    $html = '
        <a href="javascript:void(0);" data-toggle="modal" data-target="#' . $modalId . '">
            <i class="fa fa-qrcode ml-3" style="color:#ccc;"></i>
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
                        <p class="mt-2 small text-muted">Scan to open attendance link</p>
                    </div>
                </div>
            </div>
        </div>';

    return $html;
})
            ->editColumn('lesson_image', function ($q) {
                return ($q->lesson_image != null) ? '<img height="50px" src="' . asset('storage/uploads/' . $q->lesson_image) . '">' : 'N/A';
            })
            ->editColumn('free_lesson', function ($q) {
                return ($q->free_lesson == 1) ? "Yes" : "No";
            })
            ->editColumn('published', function ($q) {
                return ($q->published == 1) ? "Yes" : "No";
            })
            ->rawColumns(['lesson_image', 'qr_code', 'attendance', 'actions' , 'course'])
            ->make();
    }

    /**
     * Show the form for creating new Lesson.
     *
     * @return \Illuminate\Http\Response
     */

    public function selectCourse()
{
    if (!Gate::allows('lesson_create')) {
        return abort(401);
    }

    $courses = Course::has('category')->orderBy('title')->get();
     return view('backend.lessons.select-course', compact('courses'));
}

    public function create(Request $request)
    {
        //dd($request->all());

        if (!Gate::allows('lesson_create')) {
            return abort(401);
        }
        $course = null;
        if ($request->course_id) {
            $course = Course::find($request->course_id);

            if (!$course) {
                abort(404, 'Course not found');
            }
        }

        //dd( $course); 

        $courses = Course::has('category')->get()->pluck('title', 'id')->prepend('Please select', '');
        $courses_all = null;
        $temp_id = uniqid();
        return view('backend.lessons.create', compact('courses' , 'courses_all','temp_id' , 'course'));
    }

    /**
     * Store a newly created Lesson in storage.
     *
     * @param  \App\Http\Requests\StoreLessonsRequest $request
     * @return \Illuminate\Http\Response
     */

    public function checkCourse(Request $request)
{
    $course = Course::with('category')->find($request->id);

    return response()->json([
        'success' => true,
        'category' => $course->category->name ?? null,
        'start_date' => $course->start_date,
        'end_date' => $course->end_date

    ]);
}

    public function store(StoreLessonsRequest $request)
{
    if (!Gate::allows('lesson_create')) {
        return abort(401);
    }

    $titles = $request->input('title', []);
    $count = is_array($titles) ? count($titles) : 0;

    if ($count < 1) {
        return response()->json([
            'status' => 'error',
            'clientmsg' => 'No lesson title received.'
        ], 422);
    }

    DB::beginTransaction();

    try {
        for ($i = 0; $i < $count; $i++) {

            $slug = uniqid() . Str::slug($request->title[$i]);

            if (Lesson::where('slug', $slug)->exists()) {
                throw new Exception("Slug already exists");
            }

            $lesson_data = $request->except([
                'downloadable_files', 'lesson_image', 'slug',
                'title', 'arabic_title', 'short_text',
                'full_text', 'duration', 'lesson_start_date', 'videos'
            ]) + [
                'position' => (Lesson::where('course_id', $request->course_id)->max('position') ?? 0)+ 1,
                'published' => (int) $request->boolean('published')
            ];

            $lesson = Lesson::create($lesson_data);

            $lesson->update([
                'temp_id' => $request->temp_id,
                'slug' => $slug,
                'title' => $request->title[$i],
                'arabic_title' => $request->arabic_title[$i] ?? null,
                'duration' => $request->duration[$i] ?? null,
                'short_text' => $request->short_text[$i] ?? null,
                'full_text' => $request->full_text[$i] ?? null,
                'lesson_start_date' => !empty($request->lesson_start_date[$i])
                    ? date('Y-m-d H:i', strtotime($request->lesson_start_date[$i]))
                    : null,
            ]);
                $lessonVideos = $request->input("videos.$i", []);

    foreach ($lessonVideos as $index => $video) {

        $filePath = null;

        if ($request->hasFile("videos.$i.$index.file")) {
            $filePath = $request->file("videos.$i.$index.file")
                ->store('lesson_videos', 'public');
        }

        LessonVideo::create([
            'lesson_id' => $lesson->id,
            'title' => $video['title'] ?? null,
            'type' => $video['type'] ?? 'upload',
            'url' => $video['url'] ?? null,
            'file_path' => $filePath,
            'sort_order' => $index,
            'is_preview' => isset($video['is_preview']) ? 1 : 0
        ]);
    }

            // Save files (example)
            $files_pointer = $i + 1;
            $downloadedFiles = $request->file('downloadable_files_' . $files_pointer, []);

            if (!empty($downloadedFiles)) {
                $this->saveAllFilesByLesson(
                    $downloadedFiles,
                    'downloadable_files',
                    Lesson::class,
                    $lesson,
                    $files_pointer,
                    "download_file"
                );
            }
        }

        DB::commit();

        return response()->json([
            'status' => 'success',
            'clientmsg' => 'Lessons added successfully'
        ]);

    } catch (Exception $e) {

        DB::rollBack();

        Log::error('Lesson save failed: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'clientmsg' => $e->getMessage()
        ], 500);
    }
}


    /**
     * Show the form for editing Lesson.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
public function edit($id)
{
    if (!Gate::allows('lesson_edit')) {
        return abort(401);
    }

    $courses = Course::has('category')
        ->pluck('title', 'id')
        ->prepend('Please select', '');

    $lesson = Lesson::with(['media', 'mediaVideo', 'videos'])->findOrFail($id);

    return view('backend.lessons.edit', compact('lesson', 'courses'));
}
}
    
