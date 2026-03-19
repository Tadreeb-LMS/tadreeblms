<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Exports\UserFeedbackAnswersExport;
use App\Models\Course;
use App\Models\Auth\User;
use App\Models\CourseTimeline;
use App\Models\Lesson;
use App\Models\Media;
use App\Models\Test;
use App\Helpers\CustomHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Models\CourseFeedback;
use App\Models\FeedbackQuestion;
use App\Models\UserFeedback;
use Carbon\Carbon;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class UserFeebackAnswersController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $userFeedbackAnswers = $this->buildFeedbackAnswersQuery($request);

            return DataTables::of($userFeedbackAnswers)
                ->addIndexColumn()
                ->addColumn('user_name', function ($single) {
                    return @$single->user->full_name;
                })
                ->addColumn('course_name', function ($single) {
                    return @$single->course->title;
                })
                ->addColumn('submitted_on', function ($single) {
                    return $single->created_at ? $single->created_at->format('d M Y h:i A') : '-';
                })
                ->addColumn('question_answers', function ($single) {
                    return '<a class="badge badge-info feedback-detail" data-id="' . $single->id . '" href="#"> Detail </a>'; //$single->question_answers;
                })
                ->filter(function ($query) use ($request) {
                    $this->applySearchFilter($query, $request->input('search.value'));
                })
                ->rawColumns(['question_answers'])
                ->make(true);
        }

        $courseFeedbackTable = (new CourseFeedback())->getTable();

        $courses = Course::whereIn('id', function ($query) use ($courseFeedbackTable) {
                $query->select('course_id')
                    ->from('user_feedback')
                ->whereExists(function ($subQuery) use ($courseFeedbackTable) {
                        $subQuery->selectRaw('1')
                    ->from($courseFeedbackTable)
                    ->whereColumn($courseFeedbackTable . '.course_id', 'user_feedback.course_id');
                    });
            })
            ->orderBy('title')
            ->pluck('title', 'id');

        $users = User::whereIn('id', function ($query) use ($courseFeedbackTable) {
                $query->select('user_id')
                    ->from('user_feedback')
                ->whereExists(function ($subQuery) use ($courseFeedbackTable) {
                        $subQuery->selectRaw('1')
                    ->from($courseFeedbackTable)
                    ->whereColumn($courseFeedbackTable . '.course_id', 'user_feedback.course_id');
                    });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email'])
            ->mapWithKeys(function ($user) {
                $label = trim($user->full_name);

                if (!empty($user->email)) {
                    $label .= ' (' . $user->email . ')';
                }

                return [$user->id => $label];
            });

        return view('backend.course_feedback_answer.index', compact('courses', 'users'));
    }

    public function export(Request $request)
    {
        $userFeedbackAnswers = $this->buildFeedbackAnswersQuery($request);

        $this->applySearchFilter($userFeedbackAnswers, $request->input('search'));

        return Excel::download(
            new UserFeedbackAnswersExport($userFeedbackAnswers->get()),
            'user-feedback-answers-' . now()->format('Y-m-d-His') . '.xlsx'
        );
    }

    private function buildFeedbackAnswersQuery(Request $request): Builder
    {
        $query = UserFeedback::select('user_feedback.*')
            ->with(['user:id,first_name,last_name,email', 'course:id,title'])
            ->whereHas('courseFeedback')
            ->whereIn('id', function ($subQuery) {
                $subQuery->selectRaw('MAX(id)')
                    ->from('user_feedback')
                    ->groupBy('user_id', 'course_id');
            })
            ->latest();

        $courseIds = collect((array) $request->input('course_ids', []));

        $legacyCourseId = $request->input('course_id');
        if (!empty($legacyCourseId) && is_numeric($legacyCourseId)) {
            $courseIds->push($legacyCourseId);
        }

        $courseIds = $courseIds
            ->filter(function ($id) {
                return is_numeric($id);
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values();

        if ($courseIds->isNotEmpty()) {
            $query->whereIn('course_id', $courseIds->all());
        }

        $userIds = collect((array) $request->input('user_ids', []))
            ->filter(function ($id) {
                return is_numeric($id);
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->values();

        if ($userIds->isNotEmpty()) {
            $query->whereIn('user_id', $userIds->all());
        }

        $fromDate = $this->parseFilterDate($request->input('date_from'));
        $toDate = $this->parseFilterDate($request->input('date_to'));

        if ($fromDate && $toDate && $fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate->copy()->startOfDay());
        }

        if ($toDate) {
            $query->where('created_at', '<=', $toDate->copy()->endOfDay());
        }

        return $query;
    }

    private function applySearchFilter(Builder $query, ?string $search): void
    {
        if (empty($search)) {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->whereHas('user', function ($q2) use ($search) {
                $q2->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
            })
            ->orWhereHas('course', function ($q2) use ($search) {
                $q2->where('title', 'like', "%{$search}%");
            });
        });
    }

    private function parseFilterDate(?string $date): ?Carbon
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (\Exception $exception) {
            return null;
        }
    }

    public function feedback_detail(Request $request, $id)
    {
        // Example: fetch feedback from DB
        
        $feedback = UserFeedback::find($id);

        if (!$feedback) {
            return response()->json(['html' => '<p>No feedback found.</p>']);
        }

        //dd($feedback->question_answers);

        return response()->json(['html' => $feedback->question_answers]);
    }

    public function destroy(Request $request)
    {
        CourseFeedback::where('course_id', $request->id)->delete();
    }

    public function edit(Request $request)
    {
        $cf = CourseFeedback::where('course_id', $request->id)->first();
        @$cf->feedback_question_id = CourseFeedback::where('course_id', $request->id)->pluck('feedback_question_id')->toArray();
        $courses = Course::all();
        $questions = FeedbackQuestion::get()->pluck('question', 'id');

        return view('backend.course_feedback_question.edit', compact('cf', 'courses', 'questions'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'course_id' => 'required|numeric|min:1|exists:courses,id',
            'feedback_question_ids' => 'array',
            'feedback_question_ids.*.' => 'numeric|min:1|exists:feedback_questions,id',
        ]);

        CourseFeedback::where('course_id', $request->course_id)->delete();

        $courseFeedback = [];
        foreach ($request->feedback_question_ids as $feedbackQuestion) {
            $courseFeedback[] = [
                'feedback_question_id' => $feedbackQuestion,
                'course_id' => $request->course_id,
                'created_by' => auth()->user()->id,
            ];
        }

        CourseFeedback::insert($courseFeedback);

        return response()->json(['status' => 'success', 'clientmsg' => 'Added successfully']);
        // return redirect()->route('admin.feedback.create_course_feedback')->withFlashSuccess(trans('alerts.backend.general.created'));
    }
}
