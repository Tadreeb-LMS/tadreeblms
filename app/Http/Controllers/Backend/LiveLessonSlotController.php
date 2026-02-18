<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Mail\Backend\LiveLesson\TeacherMeetingSlotMail;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LiveLesson;
use App\Models\LiveLessonSlot;
use App\Services\ZoomService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\Facades\DataTables;

class LiveLessonSlotController extends Controller
{
    protected $zoomService;

    public function __construct(ZoomService $zoomService)
    {
        $this->zoomService = $zoomService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('live_lesson_slot_access')) {
            return abort(401);
        }
        $liveLessons = Lesson::ofTeacher()->where('live_lesson',1)->pluck('title', 'id')->prepend('Please select', '');
        return view('backend.live-lesson-slots.index', compact('liveLessons'));
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
        $liveLessonsSlots = "";
        $liveLessonsSlots = LiveLessonSlot::query()->whereIn('lesson_id', Lesson::ofTeacher()->where('live_lesson',1)->pluck('id'));


        if ($request->live_lesson_id != "") {
            $liveLessonsSlots = $liveLessonsSlots->where('lesson_id', (int)$request->live_lesson_id)->orderBy('created_at', 'desc');
        }

        if ($request->show_deleted == 1) {
            if (!Gate::allows('live_lesson_slot_delete')) {
                return abort(401);
            }
            $liveLessonsSlots = LiveLessonSlot::query()->with('lesson')->orderBy('created_at', 'desc')->onlyTrashed();
        }


        if (auth()->user()->can('live_lesson_slot_view')) {
            $has_view = true;
        }
        if (auth()->user()->can('live_lesson_slot_edit')) {
            $has_edit = true;
        }
        if (auth()->user()->can('live_lesson_slot_delete')) {
            $has_delete = true;
        }

        return DataTables::of($liveLessonsSlots)
            ->addIndexColumn()
            ->addColumn('actions', function ($liveLessonsSlot) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.live-lesson-slots', 'label' => 'id', 'value' => $liveLessonsSlot->id]);
                }
                if ($has_view) {
                    $view = view('backend.datatable.action-view')
                        ->with(['route' => route('admin.live-lesson-slots.show', ['live_lesson_slot' => $liveLessonsSlot->id])])->render();
                }
                // Check if start_at timezone config is available or default to UTC
                $timezone = config('zoom.timezone', 'UTC');
                if ($liveLessonsSlot->start_at->timezone($timezone)->gt(Carbon::now(new \DateTimeZone($timezone)))) {
                    if ($has_edit) {
                        $edit = view('backend.datatable.action-edit')
                            ->with(['route' => route('admin.live-lesson-slots.edit', ['live_lesson_slot' => $liveLessonsSlot->id])])
                            ->render();
                        $view .= $edit;
                    }
                }

                if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.live-lesson-slots.destroy', ['live_lesson_slot' => $liveLessonsSlot->id])])
                        ->render();
                    $view .= $delete;
                }

                return $view;
            })
            ->editColumn('start_at', function ($liveLessonsSlot) {
                return $liveLessonsSlot->start_at->format('d-m-Y h:i:s A');
            })
            ->editColumn('start_url', function ($liveLessonsSlot) {
                 $timezone = config('zoom.timezone', 'UTC');
                if ($liveLessonsSlot->start_at->timezone($timezone)->lt(Carbon::now(new \DateTimeZone($timezone)))) {
                    return '<a href="#" class="btn btn-warning btn-block mb-1 text-white">'.trans('labels.backend.live_lesson_slots.closed').'</a>';
                } else {
                    return '<a href="' . $liveLessonsSlot->start_url . '" class="btn btn-success btn-block mb-1">' . trans('labels.backend.live_lesson_slots.start_url') . '</a>';
                }
            })
            ->addColumn('course', function ($liveLessonsSlot){
                return ($liveLessonsSlot->lesson->course) ? $liveLessonsSlot->lesson->course->title : 'N/A';
            })
            ->rawColumns(['actions','start_url','course'])
            ->make();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('live_lesson_slot_create')) {
            return abort(401);
        }

        $lessons = Lesson::ofTeacher()->get()->pluck('title', 'id')->prepend('Please select', '');
        return view('backend.live-lesson-slots.create', compact('lessons'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!Gate::allows('live_lesson_slot_create')){
            abort(401);
        }
        $request->validate([
            'lesson_id' => 'required',
            'topic' => 'required',
            'description' => 'required',
            'start_at' => 'required',
            'duration' => 'required',
            'password' => 'required',
            'student_limit' => 'required',
        ]);

        $meeting = $this->meetingCreateOrUpdate($request);

        if (!$meeting) {
            return back()->withFlashDanger(__('alerts.backend.general.error_zoom_connection'));
        }

        $saveField = [
            'lesson_id' => $request->lesson_id,
            'meeting_id' => $meeting->id,
            'topic' => $request->topic,
            'description' => $request->description,
            'start_at' => $request->start_at,
            'duration' => $request->duration,
            'password' => $request->password,
            'start_url' => $meeting->start_url,
            'join_url'=> $meeting->join_url,
            'student_limit' => $request->student_limit,
        ];

        $liveLessonSlot = LiveLessonSlot::create($saveField);


        $this->meetingMail($liveLessonSlot);

        return redirect()->route('admin.live-lesson-slots.index', ['lesson_id' => $request->lesson_id])->withFlashSuccess(__('alerts.backend.general.created'));

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LiveLessonSlot  $liveLessonSlot
     * @return \Illuminate\Http\Response
     */
    public function show(LiveLessonSlot $liveLessonSlot)
    {
        if(!Gate::allows('live_lesson_slot_view')){
            return abort(401);
        }
        return view('backend.live-lesson-slots.show', compact('liveLessonSlot'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LiveLessonSlot  $liveLessonSlot
     * @return \Illuminate\Http\Response
     */
    public function edit(LiveLessonSlot $liveLessonSlot)
    {
        if(!Gate::allows('live_lesson_slot_edit')){
            return abort(401);
        }
        $lessons = Lesson::ofTeacher()->get()->pluck('title', 'id')->prepend('Please select', '');
        return view('backend.live-lesson-slots.edit', compact('lessons','liveLessonSlot'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LiveLessonSlot  $liveLessonSlot
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LiveLessonSlot $liveLessonSlot)
    {
        if(!Gate::allows('live_lesson_slot_edit')){
            return abort(401);
        }
        $request->validate([
            'topic' => 'required',
            'description' => 'required',
            'start_at' => 'required',
            'duration' => 'required',
            'password' => 'required',
            'student_limit' => 'required',
        ]);
        $meeting = $this->meetingCreateOrUpdate($request, true, $liveLessonSlot->meeting_id);

        if (!$meeting) {
            return back()->withFlashDanger(__('alerts.backend.general.error_zoom_connection'));
        }

        $saveField = [
            'lesson_id' => $request->lesson_id,
            'meeting_id' => $meeting->id,
            'topic' => $request->topic,
            'description' => $request->description,
            'start_at' => $request->start_at,
            'duration' => $request->duration,
            'password' => $request->password,
            'start_url' => $meeting->start_url,
            'join_url'=> $meeting->join_url,
            'student_limit' => $request->student_limit,
        ];

        $liveLessonSlot->update($saveField);

        $this->meetingMail($liveLessonSlot);

        return redirect()->route('admin.live-lesson-slots.index', ['lesson_id' => $request->lesson_id])->withFlashSuccess(__('alerts.backend.general.updated'));

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LiveLessonSlot  $liveLessonSlot
     * @return \Illuminate\Http\Response
     */
    public function destroy(LiveLessonSlot $liveLessonSlot)
    {
        if(!Gate::allows('live_lesson_slot_delete')){
            return abort(401);
        }
        
        $this->zoomService->deleteMeeting($liveLessonSlot->meeting_id);
        
        $liveLessonSlot->forceDelete();
        return redirect()->route('admin.live-lesson-slots.index')->withFlashSuccess(__('alerts.backend.general.deleted'));
    }

    private function meetingCreateOrUpdate(Request $request, $update = false, $meetingId = null)
    {
        $user = $this->zoomService->getFirstUser();
        
        if (!$user && !$update) {
            // If user not found and creating, we can't proceed.
            // If updating, we might not need user id logic if calling patch /meetings/{id} directly?
            // Actually patch /meetings/{id} doesn't need user id.
            return null;
        }

        $meetingData = [
            'topic' => $request->topic,
            'type' => 2,
            'agenda' => $request->description,
            'duration' => $request->duration,
            'password' => $request->password,
            'start_time' => $request->start_at,
            'timezone' => config('zoom.timezone', 'UTC')
        ];

        $settings = [
            'join_before_host' => $request->change_default_setting ? ($request->join_before_host ? true: false) : (config('zoom.join_before_host')? true: false),
            'host_video' => $request->change_default_setting ? ($request->host_video ? true: false) : (config('zoom.host_video') ? true : false),
            'participant_video' => $request->change_default_setting ? ($request->participant_video ? true: false) : (config('zoom.participant_video') ? true : false),
            'mute_upon_entry' => $request->change_default_setting ? ($request->participant_mic_mute ? true: false) : (config('zoom.mute_upon_entry') ? true : false),
            'waiting_room' => $request->change_default_setting ? ($request->waiting_room ? true: false) : (config('zoom.waiting_room') ? true : false),
            'approval_type' => $request->change_default_setting ? $request->approval_type : config('zoom.approval_type'),
            'audio' => $request->change_default_setting ? $request->audio_option : config('zoom.audio'),
            'auto_recording' => config('zoom.auto_recording')
        ];

        $meetingData['settings'] = $settings;

        if($update){
            $this->zoomService->updateMeeting($meetingId, $meetingData);
            // Since update returns 204, we need to construct response or fetch.
            // Optimization: Just return the updated data merged with old ID. 
            // BUT we don't have old data easily unless we fetch. 
            // Or we assume start_url etc didn't change (Zoom URLs don't usually change on update).
            // Let's refetch to be safe, or just return an object with ID and what we have? 
            // The method expects an object with id, start_url, join_url.
            // If we fetch:
            $response = $this->zoomService->getMeeting($meetingId);
            if ($response->successful()) {
                return (object) $response->json();
            }
             // Fallback: return what we have (this might be risky if start_url is needed but not in $meetingData)
             // But existing code expects it.
            return null;
        }else {
             $response = $this->zoomService->createMeeting($user['id'], $meetingData);
             if ($response->successful()) {
                 return (object) $response->json();
             }
             return null;
        }
    }

    private function meetingMail($liveLessonSlot)
    {
        foreach ($liveLessonSlot->lesson->course->teachers as $teacher){
            $content = [
                'name' => $teacher->name,
                'course' => $liveLessonSlot->lesson->course->title,
                'lesson' => $liveLessonSlot->lesson->title,
                'meeting_id' => $liveLessonSlot->meeting_id,
                'password' => $liveLessonSlot->password,
                'start_at' => $liveLessonSlot->start_at,
                'start_url' => $liveLessonSlot->start_url

            ];
            \Mail::to($teacher->email)->send(new TeacherMeetingSlotMail($content));
        }
    }
}
