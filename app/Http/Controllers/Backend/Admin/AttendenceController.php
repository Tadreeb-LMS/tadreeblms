<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Models\{Course,AttendanceStudent};
use App\Models\Order;
use Hashids\Hashids;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Auth;
use DB;
use Yajra\DataTables\Facades\DataTables;

class AttendenceController extends Controller
{
    
    /**
     * Get invoice list of current user
     *
     * @param Request $request
     */
    public function getIndex(){

        $user_id = Auth::user()->id;
        $lessons_data = DB::table('course_student')->select('course_student.course_id','courses.title as course_name','courses.slug as course_slug','lessons.id','lessons.title','lessons.lesson_start_date','attendence_student.status')
        ->leftJoin('courses','courses.id','course_student.course_id')
        ->leftJoin('lessons','lessons.course_id','courses.id')
        ->leftJoin('attendence_student','attendence_student.lesson_id','lessons.id');
        $lessons_data = $lessons_data->where('user_id',$user_id)->orderBy('lessons.lesson_start_date');
        $lessons_data = $lessons_data->get();
        //dd($lessons_data);
        return view('backend.attendence.index',compact('lessons_data'));
    }

    public function setAttendence(Request $request)
    {
        $lesson_id = $request->id;
        $student_id = Auth::user()->id;
        $status = $request->checked ? 1 : 0;
        $data = DB::table('attendence_student')
                ->where('lesson_id',$lesson_id)
                ->where('student_id',$student_id)
                ->first();
        if($data) {
            DB::table('attendence_student')
            ->where('lesson_id',$lesson_id)
            ->where('student_id',$student_id)
            ->update(['status'=>$status,'date'=>date('Y-m-d H:i:s'),'created_at'=>date('Y-m-d H:i:s')]);
        } else {
            DB::table('attendence_student')
             ->insert(['lesson_id'=>$lesson_id,'student_id'=>$student_id,'status'=>$status,'date'=>date('Y-m-d H:i:s'),'created_at'=>date('Y-m-d H:i:s')]);
        }
        echo json_encode(['status'=>'success']);
    }


    /**
     * Download order invoice
     *
     * @param Request $request
     */
    public function getInvoice(Request $request)
    {


        if (auth()->check()) {
            $hashid = new Hashids('',5);
            $order_id = $hashid->decode($request->order);
            $order_id = array_first($order_id);

            $order = Order::findOrFail($order_id);
            if (auth()->user()->isAdmin() || ($order->user_id == auth()->user()->id)) {
                $file = public_path() . "/storage/invoices/" . $order->invoice->url;
                return Response::download($file);
            }
        }
        return abort(404);
    }

    public function showInvoice(Request $request){


        if (auth()->check()) {
            $hashid = new Hashids('',5);
            $order_id = $hashid->decode($request->code);
            $order_id = array_first($order_id);

            $order = Order::findOrFail($order_id);
            if (auth()->user()->isAdmin() || ($order->user_id == auth()->user()->id)) {
                return response()->file(public_path() . "/storage/invoices/" . $order->invoice->url);
            }
        }
        return abort(404);
    }

    /**
     * Attendance records are course scoped: administrators may read every
     * course, trainers only the courses assigned to them. Anything else is
     * rejected with a 403 instead of being redirected to the landing page.
     *
     * @param  int|string  $course_id
     */
    private function authorizeAttendanceAccess($course_id)
    {
        $user = auth()->user();

        if ($user && $user->isAdmin()) {
            return;
        }

        if ($user
            && $user->hasRole('teacher')
            && $user->courses()->where('courses.id', (int) $course_id)->exists()) {
            return;
        }

        abort(403);
    }

    public function attendance_list(Request $request,$course_id,$lesson_id)
    {
       $this->authorizeAttendanceAccess($course_id);

       return view('backend.attendance_student.index',['course_id'=>$course_id,'lesson_id' =>$lesson_id]);
    }

    public function get_data(Request $request,$course_id,$lesson_id)
    {
       
        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $lessons = "";

        $this->authorizeAttendanceAccess($course_id);

        $lessons = AttendanceStudent::with('lesson','course','user')
            ->where('course_id',$course_id)
            ->where('lesson_id',$lesson_id);
        return DataTables::of($lessons)
            ->addIndexColumn()
            // ->addColumn('actions', function ($q) {
            //     return '--';
                
            // })
            ->addColumn('title', function ($q) {
                return $q->lesson->title;
                
            })
            ->addColumn('course', function ($q) {
                return $q->course->title;
                
            })
            ->addColumn('duration', function ($q) {
                return $q->lesson->duration.' [minutes]';
                
            })
            ->addColumn('username', function ($q) {
                return $q->user->email;
                
            })
            
            ->rawColumns(['title', 'course' ,'duration','username'])
            ->make();
        
    }

    
}
