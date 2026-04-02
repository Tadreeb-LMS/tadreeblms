<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StoreLessonsRequest;
use App\Http\Requests\Admin\UpdateLessonsRequest;
use App\Models\Course;
use App\Models\CourseTimeline;
use App\Models\Lesson;
use App\Models\LessonVideo;
use App\Models\Media;
use App\Models\Test;
use App\Notifications\Backend\LessonNotification;
use App\Services\NotificationSettingsService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class LessonsController extends Controller
{
    use FileUploadTrait;

    public function index(Request $request)
    {
        if (!Gate::allows('lesson_access')) {
            return abort(401);
        }

        $courses = Course::orderBy('title')->get(['id', 'title']);

        return view('backend.lessons.index', compact('courses'));
    }

    public function getData(Request $request)
    {

        $has_view   = auth()->user()->can('lesson_view');
        $has_edit   = auth()->user()->can('lesson_edit');
        $has_delete = auth()->user()->can('lesson_delete');

        $lessons = Lesson::query()
            ->with(['attendance_list', 'course'])
            ->where(function ($query) {
                $query->where('live_lesson', 0)->orWhereNull('live_lesson');
            });

        if ($request->show_deleted == 1) {
            if (!Gate::allows('lesson_delete')) {
                return abort(401);
            }
            $lessons->onlyTrashed();
        }

        if ($request->filled('status') && in_array($request->status, ['published', 'unpublished'])) {
            $lessons->where('published', $request->status === 'published' ? 1 : 0);
        }

        if ($request->filled('course_id') && is_numeric($request->course_id)) {
            $lessons->where('course_id', (int) $request->course_id);
        }

        $lessons->orderBy('id', 'asc');

        return DataTables::of($lessons)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with([
                        'route_label' => 'admin.lessons',
                        'label'       => 'id',
                        'value'       => $q->id,
                    ]);
                }

                $actions = '<div class="action-pill">';

                if ($has_view) {
                    $actions .= '<a href="' . route('admin.lessons.show', ['lesson' => $q->id]) . '">'
                        . '<i class="fa fa-eye" aria-hidden="true"></i></a>';
                }

                if ($has_edit) {
                    $actions .= '<a href="' . route('admin.lessons.edit', ['lesson' => $q->id]) . '">'
                        . '<i class="fa fa-edit" aria-hidden="true"></i></a>';
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

                if ($courseId <= 0 || !$q->attendance_list || $q->attendance_list->isEmpty()) {
                    return 0;
                }

                return '<a href="' . route('attendance.attendance.list', [$courseId, $q->id]) . '">'
                    . 'View All (' . $q->attendance_list->count() . ')</a>';
            })
            ->addColumn('qr_code', function ($q) {
                $courseId = (int) ($q->course_id ?? optional($q->course)->id ?? 0);

                if ($courseId <= 0) {
                    return 'N/A';
                }

                $modalId    = 'qrModal_' . $q->id;
                $qrCodeHtml = \QrCode::size(200)->generate(
                    route('attendance.attendance.lesson', [$courseId, $q->id])
                );

                return '
                    <a href="javascript:void(0);" data-toggle="modal" data-target="#' . $modalId . '">
                        <i class="fa fa-qrcode ml-3" style="color:#ccc;"></i>
                    </a>
                    <div class="modal fade" id="' . $modalId . '" tabindex="-1" role="dialog"
                         aria-labelledby="qrModalLabel_' . $q->id . '" aria-hidden="true">
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
            })
            ->editColumn('lesson_image', function ($q) {
                return $q->lesson_image
                    ? '<img height="50px" src="' . asset('storage/uploads/' . $q->lesson_image) . '">'
                    : 'N/A';
            })
            ->editColumn('free_lesson', fn($q) => $q->free_lesson == 1 ? 'Yes' : 'No')
            ->editColumn('published', fn($q) => $q->published == 1 ? 'Yes' : 'No')
            ->rawColumns(['lesson_image', 'qr_code', 'attendance', 'actions'])
            ->make();
    }

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
        if (!Gate::allows('lesson_create')) {
            return abort(401);
        }

        $courses     = Course::has('category')->get()->pluck('title', 'id')->prepend('Please select', '');
        $courses_all = null;
        $temp_id     = bin2hex(random_bytes(8));

        return view('backend.lessons.create', compact('courses', 'courses_all', 'temp_id'));
    }

    public function checkCourse(Request $request)
    {
        $course = Course::with('category')->find((int) $request->id);

        return response()->json([
            'success'  => true,
            'category' => $course->category->name ?? null,
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
                'clientmsg' => 'No lesson title received. Please fill at least one lesson title and try again.'
            ], 422);
        }


        DB::beginTransaction();

        try {
            for ($i = 0; $i < $count; $i++) {
                $slug = bin2hex(random_bytes(8)) . Str::slug($request->title[$i]);

                if (Lesson::where('slug', $slug)->exists()) {
                    throw new Exception('Slug already exists.');
                }


                $lesson_data = $request->except('downloadable_files', 'lesson_image', 'slug', 'title', 'arabic_title', 'short_text', 'full_text', 'duration', 'lesson_start_date', 'videos')
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
                $rawLessonStartDate = $request->lesson_start_date[$i] ?? null;
                $lesson->lesson_start_date = !empty($rawLessonStartDate) ? date('Y-m-d H:i', strtotime($rawLessonStartDate)) : null;
                $lesson->save();

                // Save videos for this specific lesson.
                // The create form uses a global videoIndex (0, 1, 2...) across all videos.
                // For a single lesson all submitted videos belong to this lesson, so collect
                // every valid entry from videos[]. For bulk lesson creation keep the existing
                // lesson-index based mapping.
                $singleVideoKeys = ['title', 'type', 'url', 'is_preview', 'file'];
                $lessonVideos = [];

                if ($count == 1) {
                    foreach ($request->input('videos', []) as $vIdx => $vData) {
                        if (is_array($vData) && count(array_intersect($singleVideoKeys, array_keys($vData))) > 0) {
                            $lessonVideos[$vIdx] = $vData;
                        }
                    }
                } else {
                    $lessonVideosRaw = $request->input("videos.$i", []);
                    if (empty($lessonVideosRaw)) {
                        $lessonVideosRaw = $request->input('videos.' . ($i + 1), []);
                    }
                    if (is_array($lessonVideosRaw) && !empty($lessonVideosRaw)) {
                        $looksLikeSingleVideo = count(array_intersect($singleVideoKeys, array_keys($lessonVideosRaw))) > 0;
                        if ($looksLikeSingleVideo) {
                            $lessonVideos[$i] = $lessonVideosRaw;
                        } else {
                            $lessonVideos = $lessonVideosRaw;
                        }
                    }
                }

                if (count($lessonVideos) > 0) {
                    $sortOrder = 0;
                    foreach ($lessonVideos as $vIdx => $video) {
                        if (!is_array($video)) {
                            continue;
                        }

                        $filePath = null;
                        if ($request->hasFile("videos.$vIdx.file")) {
                            $filePath = $request->file("videos.$vIdx.file")
                                ->store('lesson_videos', 'public');
                        }

                        LessonVideo::create([
                            'lesson_id' => $lesson->id,
                            'title' => $video['title'] ?? null,
                            'type' => $video['type'] ?? 'upload',
                            'url' => $video['url'] ?? null,
                            'file_path' => $filePath,
                            'sort_order' => $sortOrder,
                            'is_preview' => isset($video['is_preview']) ? 1 : 0
                        ]);

                        $sortOrder++;
                    }
                }
                // Lesson added notification

                try {
                    $notificationSettings = app(NotificationSettingsService::class);
                    if ($notificationSettings->shouldNotify('lessons', 'lesson_added', 'email')) {
                        $lessonCourse = Course::find($request->course_id);
                        LessonNotification::sendLessonAddedEmail(\Auth::user(), $lesson, $lessonCourse);
                        LessonNotification::createLessonAddedBell(\Auth::user(), $lesson, $lessonCourse);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send lesson added notification: ' . $e->getMessage());
                }

                $filesPointer    = $i + 1;
                $mediaTypes      = $request->input('media_type_' . $filesPointer, []);
                $videoFiles      = $request->file('video_file_' . $filesPointer, []);
                $downloadedFiles = $request->file('downloadable_files_' . $filesPointer, []);
                $addPdfs         = $request->file('add_pdf_' . $filesPointer, []);
                $audioFiles      = $request->file('add_audio_' . $filesPointer, []);

                if (!empty($downloadedFiles)) {
                    $this->saveAllFilesByLesson($downloadedFiles, 'downloadable_files', Lesson::class, $lesson, $filesPointer, 'download_file');
                }


                if (!empty($addPdfs)) {
                    $this->saveAllFilesByLesson($addPdfs, 'add_pdf', Lesson::class, $lesson, $filesPointer, 'lesson_pdf');
                }

                if (!empty($audioFiles)) {
                    $this->saveAllFilesByLesson($audioFiles, 'add_audio', Lesson::class, $lesson, $filesPointer, 'lesson_audio');
                }

                if (!empty($mediaTypes)) {
                    foreach ($mediaTypes as $mediaType) {
                        if (in_array($mediaType, ['youtube', 'vimeo', 'embed'])) {
                            $videoUrl = trim((string) $request->video);
                            $name     = $lesson->title . ' - video';
                            Media::create([
                                'model_type' => Lesson::class,
                                'model_id'   => $lesson->id,
                                'name'       => $name,
                                'url'        => $videoUrl,
                                'type'       => $mediaType,
                                'file_name'  => $name,
                                'size'       => 0,
                            ]);
                        }

                        if ($mediaType === 'upload') {
                            $this->saveAllFilesByLesson($videoFiles, 'video_file', Lesson::class, $lesson, $filesPointer, $mediaType);
                        }
                    }
                }

                $sequence = 1;
                if ($lesson->course->courseTimeline->count() > 0) {
                    $sequence = $lesson->course->courseTimeline->max('sequence') + 1;
                }

                if ($lesson->published == 1) {
                    $timeline = CourseTimeline::where('model_type', Lesson::class)
                        ->where('model_id', $lesson->id)
                        ->where('course_id', $request->course_id)
                        ->first();

                    if (!$timeline) {
                        $timeline             = new CourseTimeline();
                        $timeline->course_id  = $request->course_id;
                        $timeline->model_id   = $lesson->id;
                        $timeline->model_type = Lesson::class;
                        $timeline->sequence   = $sequence;
                        $timeline->save();
                    }
                }
            }

            Course::where('id', $request->course_id)->update(['current_step' => 'lesson-added']);

  

            DB::commit();

            CustomHelper::updateToAllUserAssignedToCourse($request->course_id);

            return response()->json([
                'status'     => 'success',
                'temp_id'    => $request->temp_id,
                'media_type' => $request->media_type,
                'clientmsg'  => 'Added successfully',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Lesson save failed: ' . $e->getMessage());

            return response()->json(['status' => 'error', 'clientmsg' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        if (!Gate::allows('lesson_edit')) {
            return abort(401);
        }


        $courses    = Course::has('category')->get()->pluck('title', 'id')->prepend('Please select', '');
        $lesson     = Lesson::with(['media', 'mediaVideo'])->findOrFail($id);
        $videos     = $lesson->media ? $lesson->media()->pluck('url')->implode(',') : '';
        $mediavideo = $lesson->mediaVideo;

        return view('backend.lessons.edit', compact('mediavideo', 'lesson', 'courses', 'videos'));
    }

    public function update(UpdateLessonsRequest $request, $id)
    {
        if (!Gate::allows('lesson_edit')) {
            return abort(401);
        }

        DB::beginTransaction();

        try {
            $slug = blank($request->slug) ? Str::slug($request->title) : $request->slug;

            if (Lesson::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                return back()->withFlashDanger(__('alerts.backend.general.slug_exist'));
            }

            $lesson                    = Lesson::findOrFail($id);
            $lesson->update($request->except('downloadable_files', 'lesson_image'));
            $lesson->slug              = $slug;
            $lesson->duration          = $request->duration;
            $lesson->lesson_start_date = date('Y-m-d H:i', strtotime($request->lesson_start_date));
            $lesson->save();

            try {
                $notificationSettings = app(NotificationSettingsService::class);
                if ($notificationSettings->shouldNotify('lessons', 'lesson_updated', 'email')) {
                    $lessonCourse = Course::find($lesson->course_id);
                    LessonNotification::sendLessonUpdatedEmail(\Auth::user(), $lesson, $lessonCourse);
                    LessonNotification::createLessonUpdatedBell(\Auth::user(), $lesson, $lessonCourse);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send lesson updated notification: ' . $e->getMessage());
            }

            if (!blank($request->media_type)) {
                $name  = $lesson->title . ' - video';
                $media = $lesson->mediavideo ?: new Media();

                if ($request->media_type !== 'upload') {
                    $url      = $request->video;
                    $videoId  = in_array($request->media_type, ['youtube', 'vimeo'])
                        ? array_last(explode('/', $request->video))
                        : '';

                    $media->model_type = Lesson::class;
                    $media->model_id   = $lesson->id;
                    $media->name       = $name;
                    $media->url        = $url;
                    $media->type       = $request->media_type;
                    $media->file_name  = $videoId;
                    $media->size       = 0;
                    $media->save();
                }

                if ($request->media_type === 'upload' && \Illuminate\Support\Facades\Request::hasFile('video_file')) {
                    $file     = \Illuminate\Support\Facades\Request::file('video_file');
                    $filename = time() . '-' . $file->getClientOriginalName();

                    try {
                        $url = CustomHelper::uploadToS3($file, $filename);
                    } catch (Exception $e) {
                        throw new Exception('The video could not be uploaded.');

                    }

                    $media = Media::query()
                        ->where('model_type', Lesson::class)
                        ->where('model_id', $lesson->id)
                        ->first() ?? new Media();

                    $media->model_type = Lesson::class;
                    $media->model_id   = $lesson->id;
                    $media->name       = $name;
                    $media->url        = $url;
                    $media->aws_url    = $url;
                    $media->type       = $request->media_type;
                    $media->file_name  = $filename;
                    $media->size       = 0;
                    $media->save();
                }
            }


            if ($request->hasFile('add_pdf')) {
                optional($lesson->mediaPDF)->delete();
            }

            $this->saveAllFiles($request, 'downloadable_files', Lesson::class, $lesson);

            $sequence = 1;
            if ($lesson->course->courseTimeline->count() > 0) {
                $sequence = $lesson->course->courseTimeline->max('sequence') + 1;
            }

            if ((int) $request->published === 1) {
                $timeline = CourseTimeline::where('model_type', Lesson::class)
                    ->where('model_id', $lesson->id)
                    ->where('course_id', $request->course_id)
                    ->firstOrNew([]);

                $timeline->course_id  = $request->course_id;
                $timeline->model_id   = $lesson->id;
                $timeline->model_type = Lesson::class;
                $timeline->save();
            }

            DB::commit();

            return redirect()
                ->route('admin.lessons.index', ['course_id' => $request->course_id])
                ->withFlashSuccess(__('alerts.backend.general.updated'));
        } catch (Exception $e) {
            DB::rollBack();

            return back()->withFlashDanger('Error while updating...');
        }
    }

    public function show($id)
    {
        if (!Gate::allows('lesson_view')) {
            return abort(401);
        }

        $courses = Course::get()->pluck('title', 'id')->prepend('Please select', '');
        $tests   = Test::where('lesson_id', $id)->get();
        $lesson  = Lesson::findOrFail($id);

        return view('backend.lessons.show', compact('lesson', 'tests', 'courses'));
    }

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

    public function massDestroy(Request $request)
    {
        if (!Gate::allows('lesson_delete')) {
            return abort(401);
        }

        if ($request->input('ids')) {
            Lesson::whereIn('id', $request->input('ids'))->get()->each->delete();
        }
    }

    public function restore($id)
    {
        if (!Gate::allows('lesson_delete')) {
            return abort(401);
        }

        Lesson::onlyTrashed()->findOrFail($id)->restore();

        return back()->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

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

        $lessonFile = Media::where('model_type', Lesson::class)->where('model_id', $lesson->id)->first();
        if ($lessonFile) {
            File::delete(public_path('/storage/uploads/' . $lessonFile->file_name));
        }

        $timelineStep = CourseTimeline::where('model_id', $id)
            ->where('course_id', $lesson->course->id)
            ->first();

        optional($timelineStep)->delete();

        $lesson->forceDelete();

        return back()->withFlashSuccess(trans('alerts.backend.general.deleted'));

    }
}