<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Course;
use App\Models\CourseTimeline;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class LiveLessonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('live_lesson_access')) {
            return abort(401);
        }
        $courses = $courses = Course::has('category')->ofTeacher()->pluck('title', 'id')->prepend('Please select', '');
        return view('backend.live-lessons.index', compact('courses'));
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
        $liveLessons = "";
        $liveLessons = Lesson::query()->where('live_lesson', '=', 1)->whereIn('course_id', Course::ofTeacher()->pluck('id'));


        if ($request->course_id != "") {
            $liveLessons = $liveLessons->where('course_id', (int)$request->course_id)->orderBy('created_at', 'desc');
        }

        if ($request->show_deleted == 1) {
            if (!Gate::allows('live_lesson_delete')) {
                return abort(401);
            }
            $liveLessons = Lesson::query()->where('live_lesson', '=', 1)->with('course')->orderBy('created_at', 'desc')->onlyTrashed();
        }


        if (auth()->user()->can('live_lesson_view')) {
            $has_view = true;
        }
        if (auth()->user()->can('live_lesson_edit')) {
            $has_edit = true;
        }
        if (auth()->user()->can('live_lesson_delete')) {
            $has_delete = true;
        }

        return DataTables::of($liveLessons)
            ->addIndexColumn()
            ->addColumn('actions', function ($liveLesson) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.live-lessons', 'label' => 'id', 'value' => $liveLesson->id]);
                }
                if ($has_view) {
                    $view = view('backend.datatable.action-view')
                        ->with(['route' => route('admin.live-lessons.show', ['live_lesson' => $liveLesson->id])])->render();
                }
                if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.live-lessons.edit', ['live_lesson' => $liveLesson->id])])
                        ->render();
                    $view .= $edit;
                }

                if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.live-lessons.destroy', ['live_lesson' => $liveLesson->id])])
                        ->render();
                    $view .= $delete;
                }

                if (auth()->user()->can('live_lesson_view')) {
                    if ($liveLesson->test != "") {
                        $view .= '<a href="' . route('admin.tests.index', ['lesson_id' => $liveLesson->id]) . '" class="btn btn-success btn-block mb-1">' . trans('labels.backend.tests.title') . '</a>';
                    }
                }

                return $view;
            })
            ->editColumn('course', function ($liveLesson) {
                return ($liveLesson->course) ? $liveLesson->course->title : 'N/A';
            })
            ->rawColumns(['actions'])
            ->make();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('live_lesson_create')) {
            return abort(401);
        }
        $teachers = User::whereHas('roles', function ($q) {
            $q->where('role_id', 2);
        })->get()->pluck('name', 'id');
        $courses = Course::has('category')->ofTeacher()->get()->pluck('title', 'id')->prepend('Please select', '');
        return view('backend.live-lessons.create', compact('courses', 'teachers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!Gate::allows('live_lesson_create')) {
            return abort(401);
        }
        $request->validate([
            'course_id' => 'required',
            'title' => 'required',
            'short_text' => 'required'
        ]);

        $slug = Str::slug($request->title);

        $slug_lesson = Lesson::where('slug', '=', $slug)->first();

        if ($slug_lesson != null) {
            return back()->withFlashDanger(__('alerts.backend.general.slug_exist'));
        }

        $liveLesson = Lesson::create($request->all());

        $liveLesson->slug = $slug;
        $liveLesson->live_lesson = 1;
        $liveLesson->published = 1;
        $liveLesson->save();


        $this->courseTimeLine($request, $liveLesson);

        return redirect()->route('admin.live-lessons.index', ['course_id' => $request->course_id])->withFlashSuccess(__('alerts.backend.general.created'));
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Lesson $liveLesson
     * @return \Illuminate\Http\Response
     */
    public function show(Lesson $liveLesson)
    {
        if (!Gate::allows('live_lesson_view')) {
            return abort(401);
        }
        return view('backend.live-lessons.show', compact('liveLesson'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Lesson $liveLesson
     * @return \Illuminate\Http\Response
     */
    public function edit(Lesson $liveLesson)
    {
        if (!Gate::allows('live_lesson_edit')) {
            return abort(401);
        }
        $teachers = User::whereHas('roles', function ($q) {
            $q->where('role_id', 2);
        })->get()->pluck('name', 'id');
        $courses = Course::has('category')->ofTeacher()->get()->pluck('title', 'id')->prepend('Please select', '');
        return view('backend.live-lessons.edit', compact('courses', 'liveLesson', 'teachers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Lesson $liveLesson
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Lesson $liveLesson)
    {
        if (!Gate::allows('live_lesson_edit')) {
            return abort(401);
        }
        $request->validate([
            'course_id' => 'required',
            'title' => 'required',
            'short_text' => 'required'
        ]);

        $slug = Str::slug($request->title);

        $slug_lesson = Lesson::where('slug', '=', $slug)->where('id', '!=', $liveLesson->id)->first();

        if ($slug_lesson != null) {
            return back()->withFlashDanger(__('alerts.backend.general.slug_exist'));
        }

        $liveLesson->update($request->all());

        $this->courseTimeLine($request, $liveLesson);

        return redirect()->route('admin.live-lessons.index', ['course_id' => $request->course_id])->withFlashSuccess(__('alerts.backend.general.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Lesson $liveLesson
     * @return \Illuminate\Http\Response
     */
    public function destroy(Lesson $liveLesson)
    {
        if (!Gate::allows('live_lesson_delete')) {
            return abort(401);
        }
        $liveLesson->chapterStudents()->where('course_id', $liveLesson->course_id)->forceDelete();
        $liveLesson->delete();
        return back()->withFlashSuccess(__('alerts.backend.general.deleted'));
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (!Gate::allows('live_lesson_delete')) {
            return abort(401);
        }
        $liveLesson = Lesson::onlyTrashed()->findOrFail($id);
        $liveLesson->restore();

        return back()->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanent remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function permanent($id)
    {
        if (!Gate::allows('live_lesson_delete')) {
            return abort(401);
        }
        $liveLesson = Lesson::onlyTrashed()->findOrFail($id);
        $timelineStep = CourseTimeline::where('model_id', '=', $id)
            ->where('course_id', '=', $liveLesson->course->id)->first();
        if ($timelineStep) {
            $timelineStep->delete();
        }

        $liveLesson->forceDelete();

        return back()->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    private function courseTimeLine(Request $request, $liveLesson)
    {
        $sequence = 1;
        if (count($liveLesson->course->courseTimeline) > 0) {
            $sequence = $liveLesson->course->courseTimeline->max('sequence');
            $sequence = $sequence + 1;
        }

        $timeline = CourseTimeline::where('model_type', '=', Lesson::class)
            ->where('model_id', '=', $liveLesson->id)
            ->where('course_id', $request->course_id)->first();
        if ($timeline == null) {
            $timeline = new CourseTimeline();
        }
        $timeline->course_id = $request->course_id;
        $timeline->model_id = $liveLesson->id;
        $timeline->model_type = Lesson::class;
        $timeline->sequence = $sequence;
        $timeline->save();
    }
}
