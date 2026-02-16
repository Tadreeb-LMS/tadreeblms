<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\{Certificate, courseAssignment, UserCourseDetail};
use App\Models\Course;
use App\Models\Auth\User;
use App\Models\Stripe\SubscribeCourse;
use Carbon\Carbon;
use CustomHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Yajra\DataTables\Facades\DataTables;
use App\Notifications\Backend\CertificateNotification;
use App\Services\NotificationSettingsService;

class CertificateController extends Controller
{
    public function __construct()
    {

        $path = 'frontend';
        if (session()->has('display_type')) {
            if (session('display_type') == 'rtl') {
                $path = 'frontend-rtl';
            } else {
                $path = 'frontend';
            }
        } else if (config('app.display_type') == 'rtl') {
            $path = 'frontend-rtl';
        }
        $this->path = $path;
    }

    /**
     * Get certificates lost for purchased courses.
     */
    public function getCertificates(Request $request)
    {
        if ($request->ajax()) {

            $course_for_certificate = [];
            $user_id = auth()->user()->id ?? null;
            if($user_id) {
                $subscribe_courses = SubscribeCourse::query()
                                    ->with(['course','course.lessons','course.publishedLessons'])
                                    ->where('user_id', '=', $user_id)
                                    ->where('is_completed', '=', 1)
                                    ->whereHas('course')
                                    ->groupBy('course_id')
                                    ->get();
                foreach ($subscribe_courses as $key => $subscribe_course) {
                    if ($subscribe_course->course->grant_certificate) {
                        $course_for_certificate[] = $subscribe_course->course_id;
                    }
                }
            }

            $courses = Course::query()->whereIn('id', $course_for_certificate);
            return DataTables::of($courses)
                ->addIndexColumn()
                ->addColumn('link', function ($row) {
                    $url = route('admin.certificates.generate', ['course_id' => $row->id, 'user_id' => auth()->id()]);
                    return "<a target='_blank' class=\"btn btn-success\"
                            href=\"$url\"> " . trans('labels.backend.certificates.fields.download-certificate') .   " </a>";
                })
                ->rawColumns(['link'])
                ->make();
        }

        return view('backend.certificates.index');
    }


    /**
     * Generate certificate for completed course
     */
    public function generateCertificate_(Request $request)
    {
        //dd($request->all());
        $user_id = \Auth::id();
        $course_id = $request->course_id;
        //dd($user_id);

        $course = Course::whereHas('students', function ($query) {
            $query->where('id', \Auth::id());
        })
            ->where('id', '=', $course_id);

        /*
                $query = str_replace(array('?'), array('\'%s\''), $course->toSql());
                $query = vsprintf($query, $course->getBindings());
                dump($query);
                die;
                */

        $course = $course->first();

        //dd($course->progress());


        if (($course != null) && ($course->progress() == 100)) {
            $certificate = Certificate::firstOrCreate([
                'user_id' => auth()->user()->id,
                'course_id' => $request->course_id
            ]);

            $data = [
                'name' => auth()->user()->name,
                'course_name' => $course->title,
                'date' => Carbon::now()->format('d M, Y'),
            ];
            $certificate_name = 'Certificate-' . $course->id . '-' . auth()->user()->id . '.pdf';
            $certificate->name = auth()->user()->name;
            $certificate->url = $certificate_name;
            $certificate->save();

            $pdf = \PDF::loadView('certificate.index', compact('data'))->setPaper('', 'landscape');

            $pdf->save(public_path('storage/certificates/' . $certificate_name));

            return back()->withFlashSuccess(trans('alerts.frontend.course.completed'));
        }
        return abort(404);
    }

    public function generateCertificate(Request $request)
    {
        //dd("uu");
        $user_id = \Auth::id();
        $course_id = $request->course_id;

        $course = Course::whereHas('students', function ($query) {
            $query->where('id', \Auth::id());
        })
            ->where('id', '=', $course_id);

        $course = $course->first();
        $user_id = $request->user_id ?? auth()->id();

        

        $subscribed_course = SubscribeCourse::where(['course_id' => $course_id, 'user_id' => $user_id, 'is_completed' => 1])->firstOrFail();

        //dd($subscribed_course);

        $course = $subscribed_course->course;

        $user = User::find($user_id);

        //dd($course->grantCertificate($user_id));

        if ($course->grantCertificate($user_id)) {
            $certificate = Certificate::firstOrCreate([
                'user_id' => $user->id,
                'course_id' => $request->course_id
            ]);

           // $date = $certificate->created_at->format('Y-m-d');
            $date = $subscribed_course->completed_at->format('Y-m-d');

            $qrCode = QrCode::size(100)->generate(url("/certificate-verification?name=$user->name&date=$date"));
            $base64QrCode = base64_encode($qrCode);

            $data = [
                'name' => $user->name,
                'course_name' => $course->title,
                'date' => Carbon::parse($date)->format('d M, Y'),
                'stamp' => base64_encode(file_get_contents('certificate/assets/stamp.jpg')),
                'background' => base64_encode(file_get_contents('certificate/assets/delta-lines-bg.png')),
                'qr' => $base64QrCode,
            ];

            $certificate_name = 'Certificate-' . $course->id . '-' . $user->id . '.pdf';
            $certificate->name = $user->name;
            $certificate->url = $certificate_name;
            $certificate->save();

            $pdf = PDF::loadView('certificate.index', compact('data'));
            $pdf->setPaper('A4', 'landscape');

            return $pdf->download($certificate_name);
        }
        return abort(404);
    }



    public function applyCertificate(Request $request)
    {
        //dd($request->all());
        UserCourseDetail::firstOrCreate(
            [
                'user_id' => auth()->user()->id,
                'course_id' => $request->course_id,
                'status' => 'completed',
                'issue_certificate' => 'no',
                'completed_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]
        );
        $this->generateCertificate_($request);
        return redirect()->route('feedback.create_feedback_form', [$request->course_id, auth()->user()->id])->withFlashSuccess(trans('alerts.frontend.course.completed'));
    }

    /**
     * Download certificate for completed course
     */
    public function download(Request $request)
    {
        
        $certificate = Certificate::findOrFail($request->certificate_id);
        if ($certificate != null) {
            $file = public_path() . "/storage/certificates/" . $certificate->url;
            return Response::download($file);
        }
        return back()->withFlashDanger('No Certificate found');
    }


    /**
     * Get Verify Certificate form
     */
    public function getVerificationForm(Request $request)
    {
        session()->forget('data');
        if ($request->name && $request->date) {
            $certificates = Certificate::where('name', '=', $request->name)
                ->whereDate("created_at", $request->date)
                ->get();
            $data['certificates'] = $certificates;
            $data['name'] = $request->name;
            $data['date'] = $request->date;

            session(["data" => $data]);
        }

        return view($this->path . '.certificate-verification');
    }


    /**
     * Verify Certificate
     */
    public function verifyCertificate(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'date' => 'required'
        ]);

        $certificates = Certificate::where('name', '=', $request->name)
            ->whereDate("created_at", $request->date)
            ->get();
        $data['certificates'] = $certificates;
        $data['name'] = $request->name;
        $data['date'] = $request->date;
        session()->forget('certificates');
        return back()->with(['data' => $data]);
    }
}
