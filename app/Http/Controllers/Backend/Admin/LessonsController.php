<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Models\Course;
use App\Models\CourseTimeline;
use App\Models\Lesson;
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
        $courses = $courses = Course::has('category')->pluck('title', 'id')->prepend('Please select', '');

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
        $lessons = "";
        $lessons = Lesson::query()->with('attendance_list')->where('live_lesson', '=', 0)->whereIn('course_id', Course::pluck('id'));



        if ($request->course_id != "") {
            $lessons = $lessons->where('course_id', (int)$request->course_id)->orderBy('id', 'asc');
        }

        if ($request->show_deleted == 1) {
            if (!Gate::allows('lesson_delete')) {
                return abort(401);
            }
            $lessons = Lesson::query()->with('attendance_list')->where('live_lesson', '=', 0)->with('course')->orderBy('id', 'asc')->onlyTrashed();
        }




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

    if (auth()->user()->can('test_view') && $q->test != "") {
        $actions .= '<a class="dropdown-item" href="' . route('admin.tests.index', ['lesson_id' => $q->id]) . '">
            <i class="fa fa-check-square-o mr-1"></i> ' . trans('labels.backend.tests.title') . '</a>';
    }

    $actions .= '</div>';
    return $actions;
})
            ->editColumn('course', function ($q) {
                return ($q->course) ? $q->course->title : 'N/A';
            })
            ->addColumn('attendance', function ($q) {
                if (isset($q->attendance_list) && count($q->attendance_list)) {
                    return $q->attendance_list ? '<a href="' . route('attendance.attendance.list', [$q->course->id, $q->id]) . '">View All (' . count($q->attendance_list) . ')</a>' : 0;
                } else {
                    return 0;
                }
            })
            // ->addColumn('qr_code', function ($q) {
            //     return QrCode::size(80)->generate(route('attendance.attendance.lesson', [$q->course->id, $q->id]));
            // })
            ->addColumn('qr_code', function ($q) {
    $modalId = 'qrModal_' . $q->id;

    // Use original logic to generate the QR code
    $qrCodeHtml = \QrCode::size(200)->generate(route('attendance.attendance.lesson', [$q->course->id, $q->id]));

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
            ->rawColumns(['lesson_image', 'qr_code', 'attendance', 'actions'])
            ->make();
    }

    /**
     * Show the form for creating new Lesson.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //dd($request->all());

        if (!Gate::allows('lesson_create')) {
            return abort(401);
        }
        $courses = Course::has('category')->get()->pluck('title', 'id')->prepend('Please select', '');
        $course_id = $request->course_id;

        $temp_id = $request->uuid ?? null;

        if ($request->ajax()) {
            $courses = Course::where('temp_id', $temp_id)->where('id',$course_id)->first();
            $checkCat = Category::where('id', $courses->category_id)->first();
            if ($checkCat) {
                $data = ['success' => true, 'category' => $checkCat->name];
            } else {
                $data = ['success' => false, 'category' => 'not found!!'];
            }
            return $data;
        }

        $course = Course::with('latestModuleWeightage')->where('id',$course_id)->first();

        //dd( $course);

        $courses_all = Course::has('category')->get()->pluck('title', 'category_id')->prepend('Please select', '');
        return view('backend.lessons.create', compact('courses', 'courses_all', 'temp_id', 'course'));
    }

    /**
     * Store a newly created Lesson in storage.
     *
     * @param  \App\Http\Requests\StoreLessonsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLessonsRequest $request)
    {
        //dd("jj");
        if (!Gate::allows('lesson_create')) {
            return abort(401);
        }
        //dd($request->title);
        $count = count($request->title);

        DB::beginTransaction();

        //dd($request->all());

        //dd($request->all(), );

        try {
            for ($i = 0; $i < $count; $i++) {
                $slug = "";
                
                
                $slug = uniqid() . $request->title[$i];
                

                $slug_lesson = Lesson::where('slug', '=', $slug)->first();
                if ($slug_lesson != null) {
                    throw new Exception("Slug is already exits");
                }


                $lesson_data = $request->except('downloadable_files', 'lesson_image', 'slug', 'title', 'arabic_title', 'short_text', 'full_text')
                + ['position' => Lesson::where('course_id', $request->course_id)->max('position') + 1];

                //dd($lesson_data);
                
                $lesson = Lesson::create($lesson_data);
                $temp_id = $request->temp_id ?? null;
                $lesson->temp_id = $temp_id;
                $lesson->slug = $slug;
                $lesson->title = $request->title[$i];
                $lesson->arabic_title = $request->arabic_title[$i] ?? null;
                $lesson->duration = $request->duration[$i] ?? null;
                $lesson->short_text = $request->short_text[$i] ?? null;
                $lesson->full_text = $request->full_text[$i] ?? null;
                $lesson->lesson_start_date = $request->lesson_start_date ? date('Y-m-d H:i', strtotime($request->lesson_start_date)) : null;
                $lesson->save();

                

                $media = null;

                //dd($request->downloadable_files, $lesson->id);
                $lession_iddd = $lesson->id;
                $files_pointer = $i + 1;
                //dd($files_pointer);
                $mediaTypes = $request->input('media_type_' . $files_pointer, []);

                $video_files = $request->file('video_file_' . $files_pointer, []);

                $downloadedFiles = $request->file('downloadable_files_' . $files_pointer, []);

                $addPdfs = $request->file('add_pdf_' . $files_pointer, []);

                //dd($addPdfs);

                $audioFiles = $request->file('add_audio_' . $files_pointer, []);

                //dd($downloadedFiles);

                if (count($downloadedFiles)) {
                    
                    $this->saveAllFilesByLesson($downloadedFiles, 'downloadable_files', Lesson::class, $lesson, $files_pointer, "download_file");
                    
                }

                if (count($addPdfs)) {
                    
                    $this->saveAllFilesByLesson($addPdfs, 'add_pdf', Lesson::class, $lesson, $files_pointer, "lesson_pdf");
                    
                }

                if (count($audioFiles)) {
                    
                    $this->saveAllFilesByLesson($audioFiles, 'add_audio', Lesson::class, $lesson, $files_pointer, "lesson_audio");
                    
                }

                //dd($video_files);

                //Saving  videos
                if (count($mediaTypes)) {
                    foreach($mediaTypes as $media) {
                        

                        if (($media == 'youtube') || ($media == 'vimeo')) {
                            //$video_url = array_last(explode('/', $request->video));
                            parse_str(parse_url($request->video, PHP_URL_QUERY), $queryParams);
                            $video_url = $queryParams['v'] ?? null;
                            $name = $lesson->title . ' - video';
                            Media::create([
                                'model_type' => Lesson::class,
                                'model_id'   => $lesson->id,
                                'name'       => $name,
                                'url'        => $video_url,
                                'type'       => $media,
                                'file_name'  => $name,
                                'size'       => 0,
                            ]);
                        }
                        
                        if ($media == 'embed') {
                            $video_url = array_last(explode('/', $request->video));
                            $name = $lesson->title . ' - video';
                            Media::create([
                                'model_type' => Lesson::class,
                                'model_id'   => $lesson->id,
                                'name'       => $name,
                                'url'        => $video_url,
                                'type'       => $media,
                                'file_name'  => $name,
                                'size'       => 0,
                            ]);
                        }

                        if($media == 'upload') {
                            $this->saveAllFilesByLesson($video_files, 'video_file', Lesson::class, $lesson, $files_pointer, $media);
                        }

                        

                    }

                }
                    
               
                

                //$request = $this->saveAllFiles($request, 'downloadable_files', Lesson::class, $lesson);

                

                

                $sequence = 1;
                if (count($lesson->course->courseTimeline) > 0) {
                    $sequence = $lesson->course->courseTimeline->max('sequence');
                    $sequence = $sequence + 1;
                }

                if ($lesson->published == 1) {
                    $timeline = CourseTimeline::where('model_type', '=', Lesson::class)
                        ->where('model_id', '=', $lesson->id)
                        ->where('course_id', $request->course_id)->first();
                    if ($timeline == null || empty($timeline)) {
                        $timeline = new CourseTimeline();
                        $timeline->course_id = $request->course_id;
                        $timeline->model_id = $lesson->id;
                        $timeline->model_type = Lesson::class;
                        $timeline->sequence = $sequence;
                        $timeline->save();
                    }
                }

               
                
            }

            //dd();

            //Update Course step
            Course::where('id',$request->course_id)->update([
                'current_step' => 'lesson-added'
            ]);

            $course = Course::with('latestModuleWeightage')->find($request->course_id);

            //dd("kk");

            DB::commit();

            // update the progress instantly
            CustomHelper::updateToAllUserAssignedToCourse($request->course_id);

            return response()->json(['status' => 'success', 'temp_id' =>$temp_id, 'media_type' => $request->media_type, 'clientmsg' => 'Added successfully']);
        } catch (Exception $e) {
            DB::rollBack(); 
            Log::error('Lesson save failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'clientmsg' => 'Error: ' . $e->getMessage()], 500);
        }
        
        // return redirect()->route('admin.assessment_accounts.new-assisment', ['course_id' => $request->course_id])->withFlashSuccess(__('Attach test or assisment for course'));
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
        $videos = '';
        $courses = Course::has('category')->get()->pluck('title', 'id')->prepend('Please select', '');

        $lesson = Lesson::with(['media','mediaVideo'])->findOrFail($id);

        //dd( $lesson );

        if ($lesson->media) {
            //$videos = $lesson->media()->where('media.type', '=', 'YT')->pluck('url')->implode(',');
            $videos = $lesson->media()->pluck('url')->implode(',');
        }
        $lesson_media = $lesson->media;
        //dd($lesson_media);
        $mediavideo =  $lesson->mediaVideo;
        //dd($lesson_media[0]->type);
        //dd($lesson->media()->pluck('url')->implode(','),$videos);
        return view('backend.lessons.edit', compact('mediavideo', 'lesson', 'courses', 'videos'));
    }

    /**
     * Update Lesson in storage.
     *
     * @param  \App\Http\Requests\UpdateLessonsRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLessonsRequest $request, $id)
    {
        if (!Gate::allows('lesson_edit')) {
            return abort(401);
        }

        DB::beginTransaction();

        try{
           
            $slug = "";
            if (($request->slug == "") || $request->slug == null) {
                $slug = str_slug($request->title);
            } elseif ($request->slug != null) {
                $slug = $request->slug;
            }

            $slug_lesson = Lesson::where('slug', '=', $slug)->where('id', '!=', $id)->first();
            if ($slug_lesson != null) {
                return back()->withFlashDanger(__('alerts.backend.general.slug_exist'));
            }

            $lesson = Lesson::findOrFail($id);
            $lesson->update($request->except('downloadable_files', 'lesson_image'));
            $lesson->slug = $slug;
            $lesson->duration = $request->duration;
            $lesson->lesson_start_date = date('Y-m-d H:i', strtotime($request->lesson_start_date));
            $lesson->save();

            //throw new Exception('This is an intentional exception for testing purposes.');
            //dd("update");
            //Saving  videos
            if ($request->media_type != "") {
                $model_type = Lesson::class;
                $model_id = $lesson->id;
                $size = 0;
                $media = '';
                $url = '';
                $video_id = '';
                $name = $lesson->title . ' - video';
                $media = $lesson->mediavideo;
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
                        $filename = $lesson->title . ' - video';
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
                    if (\Illuminate\Support\Facades\Request::hasFile('video_file')) {
                        $file = \Illuminate\Support\Facades\Request::file('video_file');
                        $filename = time() . '-' . $file->getClientOriginalName();
                        $size = $file->getSize() / 1024;
                        $path = public_path() . '/storage/uploads/';

                        try {
                            //throw new Exception("Intentional error for testing.");
                            //$file->move($path, $filename);
                            $url = CustomHelper::uploadToS3($file, $filename);
                        } catch (Exception $e) {
                            throw new Exception("The video is not uploaded"); 
                        }

                        $video_id = $filename;
                        //$url = asset('storage/uploads/' . $filename);

                        $media = Media::query()
                            //->where('type', '=', $request->media_type)
                            ->where('model_type', '=', 'App\Models\Lesson')
                            ->where('model_id', '=', $lesson->id)
                            ->first();

                        //dd($media);

                        if (!$media) {
                            $media = new Media();
                        }
                        $media->model_type = $model_type;
                        $media->model_id = $model_id;
                        $media->name = $name;
                        $media->url = $url;
                        $media->aws_url = $url;
                        $media->type = $request->media_type;
                        $media->file_name = $video_id;
                        $media->size = 0;

                        //dd($media);

                        $media->save();
                    }
                }
            }
            if ($request->hasFile('add_pdf')) {
                $pdf = $lesson->mediaPDF;
                if ($pdf) {
                    $pdf->delete();
                }
            }


            $request = $this->saveAllFiles($request, 'downloadable_files', Lesson::class, $lesson);

            $sequence = 1;
            if (count($lesson->course->courseTimeline) > 0) {
                $sequence = $lesson->course->courseTimeline->max('sequence');
                $sequence = $sequence + 1;
            }

            if ((int)$request->published == 1) {
                $timeline = CourseTimeline::where('model_type', '=', Lesson::class)
                    ->where('model_id', '=', $lesson->id)
                    ->where('course_id', $request->course_id)->first();
                if ($timeline == null) {
                    $timeline = new CourseTimeline();
                }
                $timeline->course_id = $request->course_id;
                $timeline->model_id = $lesson->id;
                $timeline->model_type = Lesson::class;
                //$timeline->sequence = $sequence;
                $timeline->save();
            }

            DB::commit();

            return redirect()->route('admin.lessons.index', ['course_id' => $request->course_id])->withFlashSuccess(__('alerts.backend.general.updated'));

        } catch(Exception $e) {
            DB::rollBack();
            return back()->withFlashDanger("Error while updating...");
        }

        
    
    }


    /**
     * Display Lesson.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
        if (!Gate::allows('lesson_view')) {
            return abort(401);
        }
        $courses = Course::get()->pluck('title', 'id')->prepend('Please select', '');

        $tests = Test::where('lesson_id', $id)->get();

        $lesson = Lesson::findOrFail($id);


        return view('backend.lessons.show', compact('lesson', 'tests', 'courses'));
    }


    /**
     * Remove Lesson from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('lesson_delete')) {
            return abort(401);
        }
        $lesson = Lesson::findOrFail($id);
        $lesson->chapterStudents()->where('course_id', $lesson->course_id)->forceDelete();
        $lesson->delete();

        return back()->withFlashSuccess(__('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Lesson at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (!Gate::allows('lesson_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Lesson::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }


    /**
     * Restore Lesson from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (!Gate::allows('lesson_delete')) {
            return abort(401);
        }
        $lesson = Lesson::onlyTrashed()->findOrFail($id);
        $lesson->restore();

        return back()->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Lesson from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        if (!Gate::allows('lesson_delete')) {
            return abort(401);
        }
        $lesson = Lesson::onlyTrashed()->findOrFail($id);

        if (File::exists(public_path('/storage/uploads/' . $lesson->lesson_image))) {
            File::delete(public_path('/storage/uploads/' . $lesson->lesson_image));
            File::delete(public_path('/storage/uploads/thumb/' . $lesson->lesson_image));
        }

        $lesson_file = Media::where('model_type', 'App\Models\Lesson')->where('model_id', $lesson->id)->first();
        if ($lesson_file) {
            File::delete(public_path('/storage/uploads/' . $lesson_file->file_name));
        }

        $timelineStep = CourseTimeline::where('model_id', '=', $id)
            ->where('course_id', '=', $lesson->course->id)->first();
        if ($timelineStep) {
            $timelineStep->delete();
        }

        $lesson->forceDelete();



        return back()->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }
}
