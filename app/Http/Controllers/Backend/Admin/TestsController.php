<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Models\Course;
use App\Models\CourseTimeline;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTestsRequest;
use App\Http\Requests\Admin\UpdateTestsRequest;
use App\Models\Assignment;
use App\Models\TestQuestion;
use Exception;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class TestsController extends Controller
{
    /**
     * Display a listing of Test.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('test_access')) {
            return abort(401);
        }

        $courses = Course::ofTeacher()->pluck('title', 'id')->prepend('Please select', '');

        return view('backend.tests.index', compact('courses'));
    }

    public function manualTest(Request $request)
    {
        if (! Gate::allows('test_create')) {
            return abort(401);
        }
        $courses = \App\Models\Course::ofTeacher()->get();
        $courses_ids = $courses->pluck('id');
        $selected_course_id = $request->course_id;
        $courses = $courses->pluck('title', 'id')->prepend('Please select', '');
        $lessons = \App\Models\Lesson::whereIn('course_id', $courses_ids)->get()->pluck('title', 'id')->prepend('Please select', '');

        return view('backend.tests.manual-test', compact('courses', 'lessons','selected_course_id'));
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
        $tests = "";


        if ($request->course_id != "") {
            $tests = Test::query()->where('course_id', '=', $request->course_id)->orderBy('created_at', 'desc');
        }

        if (request('show_deleted') == 1) {
            if (!Gate::allows('test_delete')) {
                return abort(401);
            }
            $tests = Test::query()->onlyTrashed();
        }

        //dd($tests->get());


        if (auth()->user()->can('test_view')) {
            $has_view = true;
        }
        if (auth()->user()->can('test_edit')) {
            $has_edit = true;
        }
        if (auth()->user()->can('test_delete')) {
            $has_delete = true;
        }

        return DataTables::of($tests)
            ->addIndexColumn()
            // ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
            //     $view = "";
            //     $edit = "";
            //     $delete = "";
            //     if ($request->show_deleted == 1) {
            //         return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.tests', 'label' => 'id', 'value' => $q->id]);
            //     }
            //     if ($has_view) {
            //         $view = view('backend.datatable.action-view')
            //             ->with(['route' => route('admin.tests.show', ['test' => $q->id])])->render();
            //     }
                
            //     if($request->course_id) {
            //         $add_question = view('backend.datatable.action-add')
            //             ->with(['route' => route('admin.test_questions.index', ['course_id'=>$request->course_id, 'test_id'=>$q->id])])
            //             ->render();
            //     } else {
            //         $add_question = view('backend.datatable.action-add')
            //             ->with(['route' => route('admin.test_questions.index', ['test_id'=>$q->id])])
            //             ->render();
            //     }
                
            //     $view .= $add_question;

            //     if ($has_edit) {
            //         $edit = view('backend.datatable.action-edit')
            //             ->with(['route' => route('admin.tests.edit', ['test' => $q->id])])
            //             ->render();
            //         $view .= $edit;
            //     }



            //     if ($has_delete) {
            //         $delete = view('backend.datatable.action-delete')
            //             ->with(['route' => route('admin.tests.destroy', ['test' => $q->id])])
            //             ->render();
            //         $view .= $delete;
            //     }


            //     return $view;
            // })
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
    // Handle trashed view
    if ($request->show_deleted == 1) {
        return view('backend.datatable.action-trashed')->with([
            'route_label' => 'admin.tests',
            'label' => 'id',
            'value' => $q->id
        ]);
    }

    // Dropdown items builder
    $actions = '<div class="action-pill">';

    // View
    if ($has_view) {
        $actions .= '<a title="View" class="" href="' . route('admin.tests.show', $q->id) . '">
            <i class="fa fa-eye" aria-hidden="true"></i></a>';
    }

    // Add Question
    $addQuestionUrl = $request->course_id 
        ? route('admin.test_questions.index', ['course_id' => $request->course_id, 'test_id' => $q->id])
        : route('admin.test_questions.index', ['test_id' => $q->id]);

    $actions .= '<a title="Add Question" class="" href="' . $addQuestionUrl . '">
         <i class="fas fa-question-circle"></i></a>';

    // Edit
    if ($has_edit) {
        // $actions .= '<a title="Edit" class="" href="' . route('admin.tests.edit', $q->id) . '">
        //      <i class="fa fa-edit" aria-hidden="true"></i></a>';
    }

    // Delete
    if ($has_delete) {
        $actions .= '
            <form method="POST" action="' . route('admin.tests.destroy', $q->id) . '" class="" >
                ' . csrf_field() . method_field('DELETE') . '
                <button title="Delete" type="submit" class="" onclick="return confirm(\'Are you sure?\')">
                     <i class="fa fa-trash" aria-hidden="true"></i>
                </button>
            </form>';
    }

    $actions .= '</div>';
    // Return full dropdown HTML
    return $actions;
})
            ->addColumn('questions', function ($q) {
                // if (count($q->test_questions) > 0) {
                //     return "<span>".count($q->test_questions)."</span><a class='btn btn-success float-right' href='".route('admin.test_questions.index', ['test_id'=>$q->id])."'><i class='fa fa-arrow-circle-o-right'></i></a> ";
                // } else {
                //     return "<a target='_blank' class='btn btn-success text-center' href='".route('admin.test_questions.index', ['test_id'=>$q->id])."'><i class='fa fa-arrow-circle-o-right'></i></a>";
                // }
                return count($q->test_questions);
            })

            ->addColumn('course', function ($q) {
                return ($q->course) ? $q->course->title : "N/A";
            })

            ->addColumn('lesson', function ($q) {
                return ($q->lesson) ? $q->lesson->title : "N/A";
            })

            ->editColumn('published', function ($q) {
                return ($q->published == 1) ? "Yes" : "No";
            })
            ->rawColumns(['actions','questions'])
            ->make();
    }

    /**
     * Show the form for creating new Test.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //dd($request->all());
        if (! Gate::allows('test_create')) {
            return abort(401);
        }

        $uniqueId = uniqid();
        $selected_course_id = $request->course_id;

        $course = Course::where('id', $selected_course_id)->first();
        $test_title = $course->title . 'Test' ?? null;
        // by pass the test create
        $test = Test::create([
            'temp_id' => $uniqueId,
            'course_id' => $selected_course_id,
            'title' => $test_title,
            'description' => $test_title,
            'slug' => Str::slug($test_title),
            'passing_score' => 80
        ]);

        $redirect_url = route('admin.test_questions.create'). '/' .$selected_course_id . '/' .$uniqueId;

        if($request->test) {
            $redirect_url .= '/true';
        }
        

        // redirect
        return redirect($redirect_url);
        
        $courses = \App\Models\Course::ofTeacher()
                    //->where('published',1)
                    ->get() ?? [];
        //dd($courses);
        $courses_ids = $courses->pluck('id');
        
        

        $lessons = \App\Models\Lesson::whereIn('course_id', $courses_ids)
        ->get()->pluck('title', 'id')
        ->prepend('Please select', '');

        
        return view('backend.tests.create', compact('courses', 'lessons','selected_course_id'));
    }

    /**
     * Store a newly created Test in storage.
     *
     * @param  \App\Http\Requests\StoreTestsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTestsRequest $request)
    {
        
        $this->validate($request, 
        [
            'title' => 'required|unique:tests,title',
            'course_id_selected' => 'required'
        ]);

        if (! Gate::allows('test_create')) {
            return abort(401);
        }

        $test = Test::create($request->all());
        $test->slug = Str::slug($request->title);
        $test->save();

        $sequence = 1;
        

        if ($test->published == 1) {
            $timeline = CourseTimeline::where('model_type', '=', Test::class)
                ->where('model_id', '=', $test->id)
                ->first();
            if ($timeline == null) {
                $timeline = new CourseTimeline();
            }
            $timeline->course_id = $request->course_id_selected;
            $timeline->model_id = $test->id;
            $timeline->model_type = Test::class;
            $timeline->sequence = $sequence;
            $timeline->save();
        }

        if($request->action_btn == "submit_add_question") {
            $redirect_url = route('admin.test_questions.create'). '?test_id=' .$test->id . '&course_id=' .$request->course_id;
        } else { //done
            $redirect_url = $request->assessment_url;
        }

        return response()->json([ 'status'=>'success' , 'clientmsg' => 'Added successfully', 'redirect_url' => $redirect_url ]);
        // return redirect()->route('admin.tests.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }

    public function manualTestStore(StoreTestsRequest $request)
    {
        $this->validate($request, [
            // 'course_id' => 'required',
            'title' => 'required',
            //'description' => 'required'
        ], ['course_id.required' => 'The course field is required']);

        if (! Gate::allows('test_create')) {
            return abort(401);
        }

        //dd($request->all());

        $test = Test::create($request->all());
        $test->slug = Str::slug($request->title);
        $test->save();

        $sequence = 1;
        // if (count($test->course->courseTimeline) > 0) {
        //     $sequence = $test->course->courseTimeline->max('sequence');
        //     $sequence = $sequence + 1;
        // }

        if ($test->published == 1) {
            $timeline = CourseTimeline::where('model_type', '=', Test::class)
                ->where('model_id', '=', $test->id)
                ->first();
            if ($timeline == null) {
                $timeline = new CourseTimeline();
            }
            $timeline->course_id = $request->course_id;
            $timeline->model_id = $test->id;
            $timeline->model_type = Test::class;
            $timeline->sequence = $sequence;
            $timeline->save();
        }

        $redirect_url = route('admin.test_questions.create'). '?test_id=' .$test->id . '&course_id=' .$request->course_id . '&redirect=/user/assignments-nc/create';

        return response()->json([ 'status'=>'success' , 'clientmsg' => 'Added successfully', 'redirect_url' => $redirect_url ]);
    }


    /**
     * Show the form for editing Test.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('test_edit')) {
            return abort(401);
        }
        $courses = \App\Models\Course::ofTeacher()->get();
        $courses_ids = $courses->pluck('id');
        $courses = $courses->pluck('title', 'id')->prepend('Please select', '');
        $lessons = \App\Models\Lesson::whereIn('course_id', $courses_ids)->get()->pluck('title', 'id')->prepend('Please select', '');

        $test = Test::findOrFail($id);

        return view('backend.tests.edit', compact('test', 'courses', 'lessons'));
    }

    /**
     * Update Test in storage.
     *
     * @param  \App\Http\Requests\UpdateTestsRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTestsRequest $request, $id)
    {
        if (! Gate::allows('test_edit')) {
            return abort(401);
        }
        $test = Test::findOrFail($id);
        $test->update($request->all());
        $test->slug = Str::slug($request->title);
        $test->save();


        $sequence = 1;
        if (count($test->course->courseTimeline) > 0) {
            $sequence = $test->course->courseTimeline->max('sequence');
            $sequence = $sequence + 1;
        }

        if ($test->published == 1) {
            $timeline = CourseTimeline::where('model_type', '=', Test::class)
                ->where('model_id', '=', $test->id)
                ->where('course_id', $request->course_id)->first();
            if ($timeline == null) {
                $timeline = new CourseTimeline();
            }
            $timeline->course_id = $request->course_id;
            $timeline->model_id = $test->id;
            $timeline->model_type = Test::class;
            $timeline->sequence = $sequence;
            $timeline->save();
        }


        return redirect()->route('admin.tests.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }


    /**
     * Display Test.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (! Gate::allows('test_view')) {
            return abort(401);
        }
        $test = Test::findOrFail($id);

        return view('backend.tests.show', compact('test'));
    }


    /**
     * Remove Test from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        if (! Gate::allows('test_delete')) {
            return abort(401);
        }

        DB::beginTransaction();

        try {

            $test = Test::findOrFail($id);
            $test->chapterStudents()->where('course_id', $test->course_id)->forceDelete();
            $test->delete();

            Assignment::query()
                    ->where('test_id', $id)
                    ->delete();

            TestQuestion::query()
                    ->where('test_id', $id)
                    ->update([
                        'is_deleted' => 1
                    ]);

            TestQuestion::query()
                    ->where('test_id', $id)
                    ->delete();

            DB::commit();

        } catch(Exception $e) {
            DB::rollBack();
            return back()->withFlashSuccess($e->getMessage());
        }

        

        return back()->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Test at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (! Gate::allows('test_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Test::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }


    /**
     * Restore Test from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (! Gate::allows('test_delete')) {
            return abort(401);
        }
        $test = Test::onlyTrashed()->findOrFail($id);
        $test->restore();

        return back()->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Test from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        if (! Gate::allows('test_delete')) {
            return abort(401);
        }
        $test = Test::onlyTrashed()->findOrFail($id);
        $test->forceDelete();

        return back()->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }
}
