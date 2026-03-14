<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmailJob;
use App\Models\Auth\User;
use App\Models\Course;
use App\Models\courseAssignment;
use App\Models\Department;
use App\Models\LearningPathway;
use App\Models\LearningPathwayAssignment;
use App\Models\LearningPathwayCourse;
use App\Models\Stripe\SubscribeCourse;
use App\Models\UserLearningPathway;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\CustomValidation;
use App\Jobs\PathwayAssignmentEmail;
use App\Models\Assignment;
use App\Models\CourseAssignmentToUser;
use App\Models\CourseFeedback;

class PathwayAssignmentController extends Controller
{
    public function index(Request $request)
    {
        //dd("yes");
        if ($request->ajax()) {

            $user_id = $request->user_id ?? null;
            $course_id = $request->course_id ?? null;

            $lpa = CourseAssignmentToUser::query()
                            ->with('course','assignment','user','assignmentPathway.pathway')
                            ->where('by_pathway','1')
                            ->when(!empty($course_id), function ($q) use ($course_id) {
                                $q->where('course_id', $course_id);
                            })
                            ->when(!empty($user_id), function ($q) use ($user_id) {
                                $q->where('user_id', $user_id);
                            })
                            ->orderBy('created_at', 'Desc');
                            //->get();

            //dd($lpa);

            return DataTables::of($lpa)
                ->addColumn('pathway_title', function ($row) {
                    return @$row->assignment->title;
                })
                ->addColumn('pathway_name', function ($row) {
                    return @$row->assignmentPathway->pathway->title;
                })
                ->editColumn('course_name', function ($row) {
                    return @$row->course->title;
                })
                ->addColumn('assign_by', function ($row) {
                    return @$row->assignment->assignedBy->fullname;
                })
                ->addColumn('user_email', function ($row) {
                    return @$row->user->email;
                })
                ->addColumn('assigned_user_names', function ($row) {
                    return @$row->user->first_name . ' ' .@$row->user->last_name;
                })
                ->editColumn('created_at', function ($row) {
                    return @$row->assignment->created_at != "" ? Carbon::parse($row->assignment->created_at)->format('d/m/Y') : '-';
                })
                ->editColumn('due_date', function ($row) {
                   return @$row->assignment->due_date != "" ? Carbon::parse($row->assignment->due_date)->format('d/m/Y') : '-';
                })
                ->filter(function ($query) use ($request) {
                    // if ($request->has('search') && !empty($request->input('search.value'))) {
                    //     $search = $request->input('search.value');
                        
                    //     $query->where('title', 'like', "%{$search}%")
                    //         ->orWhereHas('assignedBy', function ($query) use ($search) {
                    //             $query->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
                    //             $query->orWhere(DB::raw("email"), 'like', "%{$search}%");
                    //         })
                    //         ->orWhereRaw("
                    //             EXISTS (
                    //                 SELECT 1
                    //                 FROM users
                    //                 WHERE FIND_IN_SET(users.id, learning_pathway_assignments.assigned_to)
                    //                 AND CONCAT(users.first_name, ' ', users.last_name) LIKE ?
                    //             )
                    //         ", ["%{$search}%"]);
                    // }
                })
                ->make();
        }

        $internal_users = User::query()
            ->where('employee_type', 'internal')
            //->where('active','1')
            ->get();

        $published_courses = Course::query()
            //->where('published', '1')
            ->get();
        return view('backend.pathway-assignment.index',[
            'internal_users' => $internal_users,
            'published_courses' => $published_courses,
        ]);
    }

    public function create()
    {
        $pathways = LearningPathway::select('id', 'title')->get();
        $teachers = User::query()->role('student')->whereIn('employee_type', ['internal'])->groupBy('email')->orderBy('created_at', 'desc')->active()->get()->pluck('name', 'id');
        $departments = Department::select('id', 'title')->get();

        return view('backend.pathway-assignment.create', compact('pathways', 'teachers', 'departments'));
    }

    public function store(Request $request)
    {
        //dd($request->all());
        try {
            // Start the transaction
            DB::beginTransaction();
            $request->validate([
                'title' => 'nullable|max:255',
                'due_date' => 'nullable',
                'teachers' => 'required_without:department_id|array|min:1',
                'teachers.*' => 'integer|exists:users,id',
                'department_id' => 'required_without:teachers|nullable|integer|exists:department,id',
                'in_sequence' => 'nullable'
            ], [
                'teachers.required_without' => 'You must provide either at least one user or a department.',
                'teachers.min' => 'The users list must contain at least one user.',
                'department_id.required_without' => 'You must provide either a department or at least one user.',
            ]);


            


            // course assignment
            $course_ids_arr = [];
            $learning_pathway_id = $request->learning_pathway_id;
            $pathway = LearningPathway::find($learning_pathway_id);
            $title = $pathway ? $pathway->title : 'New Pathway Assignment';
            $due_date = now()->addDays(30)->format('Y-m-d');
            $users = $request->teachers ?? [];
            

            if ($request->department_id) {
                $dep_users = DB::table('employee_profiles')
                    ->leftJoin('department', 'department.id', 'employee_profiles.department')
                    ->where('department.id', '=', $request->department_id)
                    ->pluck('employee_profiles.user_id')->toArray();
                $users = $dep_users;
            }

            if ($learning_pathway_id) {
                $course_ids_arr = LearningPathwayCourse::where('pathway_id', $learning_pathway_id)->pluck('course_id')->toArray();
            }

            //Chceck validation if already exits
            $already_course_assigned = CustomValidation::checkIfCourseIsAlreadyAssigned( $users, $course_ids_arr );

            if($already_course_assigned['status']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $already_course_assigned['message'], 
                    'redirect_route' => route('admin.pathway-assignments.index')
                ], 400);
            }

            $lpa = LearningPathwayAssignment::create([
                'title' => $title,
                'pathway_id' => $learning_pathway_id,
                'assigned_by' => auth()->id(),
                'assigned_to' => json_encode($users),
                'due_date' => $due_date,
            ]);

            

            foreach ($course_ids_arr as $course_id) {
                $course_Ass = courseAssignment::create([
                    'learning_pathway_assignment_id' => $lpa->id,
                    'title' => $title,
                    'due_date' => $due_date,
                    'course_id' => $course_id,
                    'assign_to' => implode(',', $users),
                    'assign_by' => auth()->id(),
                    'message' => $request->message ?? 'Pathway Assignment',
                    'assign_date' => date('Y-m-d'),
                    'is_pathway' => true,
                    'department_id' => $request->department_id,
                ]);

                if (isset($users) && (count($users) > 0)) {
                    foreach ($users as $user) {
                        $emp = User::where('id', $user)->active()->first();
                        if (!$emp) {
                            continue;
                        }

                        foreach ($course_ids_arr as $value) {


                            $has_feedback = CourseFeedback::query()
                                    ->where('course_id', $value)
                                    ->count();
                                
                            //dd($has_feedback, $course_Ass->course_id);

                            $has_assesment = Assignment::query()
                                            ->where('course_id', $value)
                                            ->count();


                            SubscribeCourse::updateOrCreate([
                                'user_id' => $user,
                                'course_id' => $value,
                               
                            ], [
                                'assign_date' => date('Y-m-d'),
                                'due_date' => $due_date,
                                'has_feedback' => $has_feedback,
                                'has_assesment' => $has_assesment,
                                'status' => 1,
                                'is_pathway' => true,
                            ]);

                            $course_assignment_user = CourseAssignmentToUser::updateOrCreate(
                                [
                                    'course_id' => $value,
                                    'user_id' => $user,
                                ],
                                [
                                    'course_assignment_id' => $course_Ass->id,
                                    'log_comment' => 'By Admin',
                                    'by_pathway' => 1,
                                ]
                            );

                            //dd($course_Ass->id, $course_assignment_user->id);

                        }

                        
                    }
                }
            }

            $recipients = [];

            foreach ($users as $key=>$user) {
                $emp = User::where('id', $user)->active()->first();
                if (!$emp) {
                    continue;
                }

                UserLearningPathway::create([
                    'pathway_id' => $learning_pathway_id,
                    'user_id' => $user,
                ]);
                $recipients[$key]['email'] =  $emp->email;
                $recipients[$key]['id'] =  $emp->id;
                $recipients[$key]['name'] =  $emp->email;

            }

            $details['subject'] = "New Assignment | Delta Academy";
            $details['assignment_title'] = $title;
            $details['site_url'] = route('admin.dashboard');

            dispatch(new PathwayAssignmentEmail($recipients, $details));

            // Commit the transaction
            DB::commit();

            return response()->json(['message' => __('Pathway assigned successfully'), 'redirect_route' => route('admin.pathway-assignments.index')]);
        } catch (ValidationException $e) {
            // Rollback the transaction in case of validation error
            DB::rollBack();

            // Return validation errors
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors(), // Returns detailed validation errors
            ], 422);
        } catch (\Exception $e) {
            // Rollback the transaction for any other exceptions
            DB::rollBack();

            // Return generic error response
            return response()->json([
                'message' => 'Failed to update pathway assignment',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $lpa = LearningPathwayAssignment::find($id);
        $pathways = LearningPathway::select('id', 'title')->get();
        $teachers = User::query()->role('student')->whereIn('employee_type', ['internal'])->groupBy('email')->orderBy('created_at', 'desc')->active()->get()->pluck('name', 'id');
        $departments = Department::select('id', 'title')->get();

        return view('backend.pathway-assignment.edit', compact('pathways', 'teachers', 'departments', 'lpa'));
    }

    public function update(Request $request, $id)
    {
        try {
            // Start the transaction
            DB::beginTransaction();
            $lpa = LearningPathwayAssignment::find($id);
            //dd($lpa);
            $request->validate([
                'title' => 'nullable|max:255', 
                'due_date' => 'nullable|max:255',
                'teachers' => 'required|array|min:1',
                'teachers.*' => 'integer|exists:users,id',
            ], [
                'teachers.required' => 'You must provide at least one user.',
                'teachers.min' => 'The users list must contain at least one user.',
            ]);

            $learning_pathway_id = $request->learning_pathway_id;
            $pathway = LearningPathway::find($learning_pathway_id);
            $title = $request->title ?? ($pathway ? $pathway->title : $lpa->title);
            $due_date = $request->due_date ?? ($lpa->due_date ?? now()->addDays(30)->format('Y-m-d'));
            $users = $request->teachers ?? [];

            $lpa->update([
                'title' => $title,
                'due_date' => $due_date,
                'pathway_id' => $learning_pathway_id,
                'assigned_to' => json_encode($users),
            ]);

            // dd([
            //     'title' => $title,
            //     'due_date' => $due_date,
            //     'pathway_id' => $learning_pathway_id,
            //     'assigned_to' => json_encode($users),
            // ]);
            $course_ids_arr = LearningPathwayCourse::where('pathway_id', $learning_pathway_id)->pluck('course_id')->toArray();

            foreach ($users as $user) {
                UserLearningPathway::updateOrCreate([
                    'pathway_id' => $learning_pathway_id,
                    'user_id' => $user,
                ]);

                foreach ($course_ids_arr as $value) {
                    SubscribeCourse::updateOrCreate([
                        'user_id' => $user,
                        'course_id' => $value,
                        'assign_date' => date('Y-m-d'),
                        'due_date' => $due_date
                    ], [
                        'status' => 1,
                        'is_pathway' => true,
                    ]);

                    $ca = courseAssignment::where([
                        'learning_pathway_assignment_id' => $lpa->id,
                        'course_id' => $value
                    ])->whereRaw("FIND_IN_SET(?, assign_to)", [$user])->first();

                    if ($ca) {
                        $ca->update([
                            'title' => $title,
                            'due_date' => $due_date,
                            'course_id' => $value,
                            'assign_to' => implode(',', $users),
                            'assign_by' => auth()->id(),
                            'assign_date' => date('Y-m-d')
                        ]);
                    } else {
                        courseAssignment::create([
                            'learning_pathway_assignment_id' => $lpa->id,
                            'course_id' => $value,
                            'title' => $title,
                            'due_date' => $due_date,
                            'course_id' => $value,
                            'assign_to' => implode(',', $users),
                            'assign_by' => auth()->id(),
                            'assign_date' => date('Y-m-d'),
                            'is_pathway' => true,
                        ]);
                    }
                }
            }

            UserLearningPathway::where('pathway_id', $learning_pathway_id)->whereNotIn('user_id', $users)->delete();

            courseAssignment::where('learning_pathway_assignment_id', $lpa->id)->whereRaw("NOT FIND_IN_SET(?, assign_to)", $users)->whereIn('course_id', $course_ids_arr)->delete();
            courseAssignment::where('learning_pathway_assignment_id', $lpa->id)->whereRaw("FIND_IN_SET(?, assign_to)", $users)->whereNotIn('course_id', $course_ids_arr)->delete();
            SubscribeCourse::pathway()->whereNotIn('user_id', $users)->whereIn('course_id', $course_ids_arr)->delete();
            SubscribeCourse::pathway()->whereIn('user_id', $users)->whereNotIn('course_id', $course_ids_arr)->delete();

            // Commit the transaction
            DB::commit();

            return response()->json(['message' => __('Pathway updated successfully'), 'redirect_route' => route('admin.pathway-assignments.index')]);
        } catch (ValidationException $e) {
            // Rollback the transaction in case of validation error
            DB::rollBack();

            // Return validation errors
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors(), // Returns detailed validation errors
            ], 422);
        } catch (\Exception $e) {
            // Rollback the transaction for any other exceptions
            DB::rollBack();

            // Return generic error response
            return response()->json([
                'message' => 'Failed to update learning pathway',
            ], 500);
        }
    }

    public function destroy($id)
    {
        $lp = LearningPathwayAssignment::find($id);
        $title = $lp->title;

        $course_ids_arr = LearningPathwayCourse::where('pathway_id', $lp->pathway_id)->pluck('course_id')->toArray();

        UserLearningPathway::where('pathway_id', $lp->pathway_id)->delete();
        SubscribeCourse::pathway()->whereIn('course_id', $course_ids_arr)->delete();
        courseAssignment::where('learning_pathway_assignment_id', $lp->id)->delete();
        $lp->delete();

        return response()->json(['message' => "$title pathway assignment deleted successfully", 'event' => "pathway_assignment_deleted"]);
    }

    // public function manageUsers($id)
    // {
    //     $lp = LearningPathway::find($id);
    //     $users = User::active()->latest()->select('id', 'first_name', 'last_name', 'email')->get();

    //     return view('backend.learning-pathway.modals.manage-users', compact('lp', 'users'));
    // }

    // public function manageUsersPost($id, Request $request)
    // {
    //     try {

    //         $learningPathway = LearningPathway::find($id);
    //         // Start the transaction
    //         DB::beginTransaction();

    //         // Validate request data
    //         $validated = $request->validate([
    //             'user_ids' => 'nullable|array', // Ensure it's an array
    //             'user_ids.*' => 'exists:users,id', // Ensure each value exists in the 'users' table
    //         ]);

    //         $this->updateCreatePathwayUsers($validated['user_ids'], $learningPathway);

    //         // Commit the transaction
    //         DB::commit();

    //         // Return success response
    //         return response()->json([
    //             'message' => "$learningPathway->title learning pathway users updated successfully",
    //         ]);
    //     } catch (ValidationException $e) {
    //         // Rollback the transaction in case of validation error
    //         DB::rollBack();

    //         // Return validation errors
    //         return response()->json([
    //             'message' => 'Validation Error',
    //             'errors' => $e->errors(), // Returns detailed validation errors
    //         ], 422);
    //     } catch (\Exception $e) {
    //         // Rollback the transaction for any other exceptions
    //         DB::rollBack();

    //         // Return generic error response
    //         return response()->json([
    //             'message' => 'Failed to update learning pathway users',
    //         ], 500);
    //     }
    // }

    // public function updateCreatePathwayUsers($users_ids_arr, $learningPathway)
    // {
    //     $users_ids_arr = $users_ids_arr ?? [];
    //     $pathway_id = $learningPathway->id;
    //     $course_ids = $learningPathway->learningPathwayCoursesOrdered->pluck('course_id')->toArray();

    //     // Assign users
    //     foreach ($users_ids_arr as $user) {
    //         UserLearningPathway::updateOrCreate(
    //             [
    //                 'pathway_id' => $pathway_id,
    //                 'user_id' => $user
    //             ],
    //         );
    //     }

    //     foreach ($course_ids as $course_id) {
    //         SubscribeCourse::updateOrCreate([
    //             'user_id' => $user,
    //             'course_id' => $course_id
    //         ], [
    //             'status' => 1,
    //             'is_pathway' => true,
    //         ]);
    //     }

    //     UserLearningPathway::where('pathway_id', $pathway_id)->whereNotIn('user_id', $users_ids_arr)->delete();
    //     SubscribeCourse::pathway()->whereIn('course_id', $course_ids)->whereNotIn('user_id', $users_ids_arr)->delete();
    // }
}
