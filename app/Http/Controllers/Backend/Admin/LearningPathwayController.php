<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Course;
use App\Models\courseAssignment;
use App\Models\LearningPathway;
use App\Models\LearningPathwayAssignment;
use App\Models\LearningPathwayCourse;
use App\Models\Stripe\SubscribeCourse;
use App\Models\UserLearningPathway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class LearningPathwayController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = LearningPathway::query();

            $data = $data->select('id', 'title', 'description', 'in_sequence')->with('learningPathwayCoursesOrdered')->latest();
            return DataTables::of($data)
                ->addColumn('courses', function ($row) {
                    return $row->learningPathwayCoursesOrdered->flatMap(fn($item) => [optional($item->course)->title])->join(', ');
                })
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && $request->search['value'] != '') {
                        $searchValue = $request->search['value'];

                        // Apply search query to the database
                        $query->where('title', 'LIKE', '%' . $searchValue . '%');
                    }
                })
                ->make();
        }
        return view('backend.learning-pathway.index');
    }

    public function create()
    {
        $courses = Course::select('id', 'title')->get();
        $users = User::active()->latest()->select('id', 'first_name', 'last_name', 'email')->get();
        return view('backend.learning-pathway.create', compact('courses', 'users'));
    }

    public function store(Request $request)
    {
        
        try {
            // Start the transaction
            DB::beginTransaction();

            // Validate request data
            $validated = $request->validate([
                'title' => [
                    'required',
                    'max:255',
                    'unique:learning_pathways,title', // Add the table name to 'unique'
                ],
                'course_id' => 'required|exists:courses,id',
                'user_ids' => 'nullable|array',
                'description' => 'nullable|max:2000',
                'in_sequence' => 'nullable'
            ], [
                'course_id.required' => 'Please select atleast one course for the pathway'
            ]);

            

            // Create Learning Pathway
            $lp = LearningPathway::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'in_sequence' => $validated['in_sequence'] ?? 0
            ]);

            $course_with_order = json_decode($request->course_with_order);

            // Create Learning Pathway Course
            foreach ($course_with_order as $key => $value) {
                LearningPathwayCourse::create([
                    'pathway_id' => $lp->id,
                    'course_id' => $value,
                    'position' => $key + 1,
                ]);
            }

            // Assign users
            // foreach (@$validated['user_ids'] as $user) {
            //     UserLearningPathway::create([
            //         'pathway_id' => $lp->id,
            //         'user_id' => $user,
            //     ]);
            // }

            // Commit the transaction
            DB::commit();

            // Return success response
            return response()->json([
                'message' => "$lp->title learning pathway created successfully",
                'redirect_route' => route('admin.learning-pathways.index')
            ]);
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
                'message' => 'Failed to create learning pathway',
            ], 500);
        }
    }

    public function edit($id)
    {
        $lp = LearningPathway::find($id);
        $courses = Course::select('id', 'title')->get();
        $users = User::active()->latest()->select('id', 'first_name', 'last_name', 'email')->get();

        return view('backend.learning-pathway.edit', compact('lp', 'courses', 'users'));
    }

    public function update(Request $request, LearningPathway $learningPathway)
    {
        
        try {
            // Start the transaction
            DB::beginTransaction();

            // Validate request data
            $validated = $request->validate([
                'title' => [
                    'required',
                    'max:255',
                    Rule::unique('learning_pathways')->ignore($learningPathway->id)
                ],
                'course_id' => 'required|exists:courses,id',
                'description' => 'nullable|max:2000',
                'user_ids' => 'nullable|array',
                'in_sequence' => 'nullable'
            ], [
                'course_id.required' => 'Please select atleast one course for the pathway'
            ]);

            // Create Learning Pathway
            $learningPathway->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'in_sequence' => $validated['in_sequence'] ?? 0
            ]);

            $courses_ids = $request->course_id ?? [];

            // checking if pathway is assigned to anyone - if yes then adding new course to their list
            $lpa_list = LearningPathwayAssignment::where('pathway_id', $learningPathway->id)->get();

            if ($lpa_list) {

                foreach($lpa_list as $lpa) {
                    $course_ids_arr = LearningPathwayCourse::where('pathway_id', $learningPathway->id)->pluck('course_id')->toArray();

                    $deleted_course_ids = collect($course_ids_arr)->diff($courses_ids)->values()->all();
    
                    $new_course_ids = collect($courses_ids)->diff($course_ids_arr)->values()->all();
    
                    //dd($courses_ids, $course_ids_arr, $deleted_course_ids, $new_course_ids);
    
    
                    $users = json_decode($lpa->assigned_to);
                    //get all users it may be multiple
                    //$users = $this->getAllAssignedUsers($learningPathway->id);
    
                    $learning_pathway_id = $learningPathway->id;
                    $title = $lpa->title;
                    $due_date = $lpa->due_date;
    
                    foreach ($users as $user) {


                        //delete course
                        foreach($deleted_course_ids as $course_id) {
                            SubscribeCourse::query()
                                                ->where('user_id', $user)
                                                ->where('course_id', $course_id)
                                                ->delete();
                        }

                        UserLearningPathway::updateOrCreate([
                            'pathway_id' => $learning_pathway_id,
                            'user_id' => $user,
                        ]);
        
                        foreach ($new_course_ids as $value) {
                            SubscribeCourse::updateOrCreate([
                                'user_id' => $user,
                                'course_id' => $value
                            ], [
                                'user_id' => $user,
                                'course_id' => $value,
                                'due_date' => $due_date,
                                'assign_date' => date('Y-m-d'),
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
                }
                
            }

            $course_with_order = json_decode($request->course_with_order);

            // Create Learning Pathway Course
            foreach ($course_with_order as $key => $value) {
                LearningPathwayCourse::updateOrCreate([
                    'pathway_id' => $learningPathway->id,
                    'course_id' => $value,
                ], [
                    'position' => $key + 1,
                ]);
            }

            LearningPathwayCourse::where('pathway_id', $learningPathway->id)->whereIn('course_id', $deleted_course_ids)->delete();

            // $this->updateCreatePathwayUsers($validated['user_ids'], $learningPathway);

            // Commit the transaction
            DB::commit();

            // Return success response
            return response()->json([
                'message' => "$learningPathway->title learning pathway updated successfully",
                'redirect_route' => route('admin.learning-pathways.index')
            ]);
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

    protected function getAllAssignedUsers($path_way_id)
    {
        $users = [];
        $users = LearningPathwayAssignment::where('pathway_id', $path_way_id)
                    ->get()
                    ->flatMap(function ($row) {
                        return json_decode($row->assigned_to, true);
                    })
                    ->unique()
                    ->values()
                    ->all();
        return $users;
    }

    public function destroy($id)
    {
        $lp = LearningPathway::find($id);
        $title = $lp->title;
        $lp->delete();

        return response()->json(['message' => "$title learning pathway deleted successfully", 'event' => "pathway_deleted"]);
    }

    public function manageUsers($id)
    {
        $lp = LearningPathway::find($id);
        $users = User::active()->latest()->select('id', 'first_name', 'last_name', 'email')->get();

        return view('backend.learning-pathway.modals.manage-users', compact('lp', 'users'));
    }

    public function manageUsersPost($id, Request $request)
    {
        try {

            $learningPathway = LearningPathway::find($id);
            // Start the transaction
            DB::beginTransaction();

            // Validate request data
            $validated = $request->validate([
                'user_ids' => 'nullable|array', // Ensure it's an array
                'user_ids.*' => 'exists:users,id', // Ensure each value exists in the 'users' table
            ]);

            // $this->updateCreatePathwayUsers($validated['user_ids'], $learningPathway);

            // Commit the transaction
            DB::commit();

            // Return success response
            return response()->json([
                'message' => "$learningPathway->title learning pathway users updated successfully",
            ]);
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
                'message' => 'Failed to update learning pathway users',
            ], 500);
        }
    }

    public function updateCreatePathwayUsers($users_ids_arr, $learningPathway)
    {
        $users_ids_arr = $users_ids_arr ?? [];
        $pathway_id = $learningPathway->id;
        $course_ids = $learningPathway->learningPathwayCoursesOrdered->pluck('course_id')->toArray();

        // Assign users
        foreach ($users_ids_arr as $user) {
            UserLearningPathway::updateOrCreate(
                [
                    'pathway_id' => $pathway_id,
                    'user_id' => $user
                ],
            );
        }

        foreach ($course_ids as $course_id) {
            SubscribeCourse::updateOrCreate([
                'user_id' => $user,
                'course_id' => $course_id
            ],[
                'status' => 1,
                'is_pathway' => true,
            ]);
        }

        UserLearningPathway::where('pathway_id', $pathway_id)->whereNotIn('user_id', $users_ids_arr)->delete();
        SubscribeCourse::pathway()->whereIn('course_id', $course_ids)->whereNotIn('user_id', $users_ids_arr)->delete();
    }
}
