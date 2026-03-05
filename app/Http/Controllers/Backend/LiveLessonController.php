```php
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

    public function index()
    {
        if (!Gate::allows('live_lesson_access')) {
            return abort(401);
        }

        $courses = Course::has('category')
            ->ofTeacher()
            ->pluck('title', 'id')
            ->prepend('Please select', '');

        return view('backend.live-lessons.index', compact('courses'));
    }


    public function getData(Request $request)
    {
        $has_view = auth()->user()->can('live_lesson_view');
        $has_edit = auth()->user()->can('live_lesson_edit');
        $has_delete = auth()->user()->can('live_lesson_delete');

        $teacherCourseIds = Course::ofTeacher()->pluck('id');

        $liveLessons = Lesson::query()
            ->where('live_lesson', 1)
            ->whereIn('course_id', $teacherCourseIds);

        if ($request->show_deleted == 1) {

            if (!Gate::allows('live_lesson_delete')) {
                return abort(401);
            }

            $liveLessons = Lesson::onlyTrashed()
                ->where('live_lesson', 1)
                ->whereIn('course_id', $teacherCourseIds);
        }

        if (!empty($request->course_id)) {
            $liveLessons->where('course_id', (int) $request->course_id);
        }

        $liveLessons->orderBy('created_at', 'desc');

        return DataTables::of($liveLessons)
            ->addIndexColumn()
            ->addColumn('actions', function ($liveLesson) use ($has_view, $has_edit, $has_delete, $request) {

                $view = "";
                $edit = "";
                $delete = "";

                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with([
                        'route_label' => 'admin.live-lessons',
                        'label' => 'id',
                        'value' => $liveLesson->id
                    ]);
                }

                if ($has_view) {
                    $view = view('backend.datatable.action-view')
                        ->with([
                            'route' => route('admin.live-lessons.show', ['live_lesson' => $liveLesson->id])
                        ])->render();
                }

                if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with([
                            'route' => route('admin.live-lessons.edit', ['live_lesson' => $liveLesson->id])
                        ])->render();

                    $view .= $edit;
                }

                if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with([
                            'route' => route('admin.live-lessons.destroy', ['live_lesson' => $liveLesson->id])
                        ])->render();

                    $view .= $delete;
                }

                if ($has_view && !empty($liveLesson->test)) {
                    $view .= '<a href="' .
                        route('admin.tests.index', ['lesson_id' => $liveLesson->id]) .
                        '" class="btn btn-success btn-block mb-1">' .
                        trans('labels.backend.tests.title') .
                        '</a>';
                }

                return $view;
            })
            ->editColumn('course', function ($liveLesson) {
                return ($liveLesson->course) ? $liveLesson->course->title : 'N/A';
            })
            ->rawColumns(['actions'])
            ->make();
    }


    public function create()
    {
        if (!Gate::allows('live_lesson_create')) {
            return abort(401);
        }

        $teachers = User::whereHas('roles', function ($q) {
            $q->where('role_id', 2);
        })->pluck('name', 'id');

        $courses = Course::has('category')
            ->ofTeacher()
            ->pluck('title', 'id')
            ->prepend('Please select', '');

        return view('backend.live-lessons.create', compact('courses', 'teachers'));
    }


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

        if (Lesson::where('slug', $slug)->exists()) {
            return back()->withFlashDanger(__('alerts.backend.general.slug_exist'));
        }

        $liveLesson = Lesson::create($request->all());

        $liveLesson->slug = $slug;
        $liveLesson->live_lesson = 1;
        $liveLesson->published = 1;
        $liveLesson->save();

        $this->courseTimeLine($request, $liveLesson);

        return redirect()
            ->route('admin.live-lessons.index', ['course_id' => $request->course_id])
            ->withFlashSuccess(__('alerts.backend.general.created'));
    }


    public function show(Lesson $liveLesson)
    {
        if (!Gate::allows('live_lesson_view')) {
            return abort(401);
        }

        return view('backend.live-lessons.show', compact('liveLesson'));
    }


    public function edit(Lesson $liveLesson)
    {
        if (!Gate::allows('live_lesson_edit')) {
            return abort(401);
        }

        $teachers = User::whereHas('roles', function ($q) {
            $q->where('role_id', 2);
        })->pluck('name', 'id');

        $courses = Course::has('category')
            ->ofTeacher()
            ->pluck('title', 'id')
            ->prepend('Please select', '');

        return view('backend.live-lessons.edit', compact('courses', 'liveLesson', 'teachers'));
    }


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

        if (Lesson::where('slug', $slug)->where('id', '!=', $liveLesson->id)->exists()) {
            return back()->withFlashDanger(__('alerts.backend.general.slug_exist'));
        }

        $liveLesson->update($request->all());

        $this->courseTimeLine($request, $liveLesson);

        return redirect()
            ->route('admin.live-lessons.index', ['course_id' => $request->course_id])
            ->withFlashSuccess(__('alerts.backend.general.updated'));
    }


    public function destroy(Lesson $liveLesson)
    {
        if (!Gate::allows('live_lesson_delete')) {
            return abort(401);
        }

        $liveLesson->chapterStudents()
            ->where('course_id', $liveLesson->course_id)
            ->forceDelete();

        $liveLesson->delete();

        return back()->withFlashSuccess(__('alerts.backend.general.deleted'));
    }


    public function restore($id)
    {
        if (!Gate::allows('live_lesson_delete')) {
            return abort(401);
        }

        Lesson::onlyTrashed()->findOrFail($id)->restore();

        return back()->withFlashSuccess(trans('alerts.backend.general.restored'));
    }


    public function permanent($id)
    {
        if (!Gate::allows('live_lesson_delete')) {
            return abort(401);
        }

        $liveLesson = Lesson::onlyTrashed()->findOrFail($id);

        $timelineStep = CourseTimeline::where('model_id', $id)
            ->where('course_id', $liveLesson->course->id)
            ->first();

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
            $sequence = $liveLesson->course->courseTimeline->max('sequence') + 1;
        }

        $timeline = CourseTimeline::where('model_type', Lesson::class)
            ->where('model_id', $liveLesson->id)
            ->where('course_id', $request->course_id)
            ->first();

        if (!$timeline) {
            $timeline = new CourseTimeline();
        }

        $timeline->course_id = $request->course_id;
        $timeline->model_id = $liveLesson->id;
        $timeline->model_type = Lesson::class;
        $timeline->sequence = $sequence;
        $timeline->save();
    }
}
```
