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


    public function generateCertificate(Request $request)
    {
        $user_id = $request->user_id ?? auth()->id();
        $course_id = $request->course_id;

        $subscribed_course = SubscribeCourse::where([
            'course_id' => $course_id,
            'user_id' => $user_id,
            'is_completed' => 1
        ])->with('course')->firstOrFail();

        $course = $subscribed_course->course;
        if (!$course) {
            abort(404);
        }

        $user = User::findOrFail($user_id);

        if (!$course->grantCertificate($user_id)) {
            abort(403, 'Certificate is not available for this course.');
        }

        $certificate = Certificate::with('course')->firstOrCreate(
            [
                'user_id' => $user_id,
                'course_id' => $course_id,
            ],
            [
                'name' => $user->name,
                'url' => 'Certificate-' . $course_id . '-' . $user_id . '.pdf',
            ]
        );

        // Keep basic certificate fields aligned for legacy records
        if (empty($certificate->name)) {
            $certificate->name = $user->name;
        }

        if (empty($certificate->url)) {
            $certificate->url = 'Certificate-' . $course_id . '-' . $user_id . '.pdf';
        }

        // Ensure validation hash exists for verification
        if (!$certificate->validation_hash) {
            $certificate->validation_hash = hash(
                'sha256',
                $user_id . '|' . $course_id . '|' . now()->timestamp . '|' . config('app.key')
            );
        }

        // Ensure human-readable certificate ID exists
        if (!$certificate->certificate_id) {
            $certificate->certificate_id = 'TLMS-' . Carbon::now()->format('Y') . '-' . str_pad($certificate->id, 6, '0', STR_PAD_LEFT);
        }

        // Preserve immutable snapshot metadata when missing
        $completedAt = $subscribed_course->completed_at ?: $subscribed_course->updated_at ?: Carbon::now();
        $completionDate = Carbon::parse($completedAt);

        $metadata = is_array($certificate->metadata) ? $certificate->metadata : [];

        if (empty($metadata)) {
            $metadata = [
                'student_name' => $user->name,
                'course_title' => $course->title,
                'completion_date' => $completionDate->toDateString(),
            ];
            $certificate->metadata = $metadata;
        }

        $certificate->save();
        $certificate->loadMissing('course');

        $metadata = is_array($certificate->metadata) ? $certificate->metadata : [];

        $data = [
            'name' => $metadata['student_name'] ?? $certificate->name ?? $user->name,
            'course_name' => $metadata['course_title'] ?? optional($certificate->course)->title ?? $course->title ?? 'Course Title',
            'date' => Carbon::parse($metadata['completion_date'] ?? $certificate->created_at)->format('d M, Y'),
            'certificate_id' => $certificate->certificate_id,
            'qr' => base64_encode(
                QrCode::size(150)
                    ->format('svg')
                    ->margin(1)
                    ->generate(url('/certificate-verification?validation_hash=' . trim($certificate->validation_hash)))
            ),
        ];

        $pdf = PDF::loadView('certificate.index', compact('data'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream("Certificate-{$certificate->certificate_id}.pdf");
    }

    /**
     * Download certificate for completed course
     */
    public function download(Request $request)
    {
        $certificateId = $request->certificate_id;
        
        // Search by primary ID or the human-readable certificate_id
        $certificate = Certificate::with('course')->where('id', $certificateId)
            ->orWhere('certificate_id', $certificateId)
            ->firstOrFail();
        
        // Bug Fix: Ensure validation_hash exists
        if (!$certificate->validation_hash) {
            $certificate->validation_hash = hash('sha256', $certificate->user_id . $certificate->course_id . now() . config('app.key'));
            $certificate->save();
        }

        // Bug Fix: Ensure human-readable ID exists
        if (!$certificate->certificate_id) {
            $certificate->certificate_id = 'TLMS-' . Carbon::now()->format('Y') . '-' . str_pad($certificate->id, 6, '0', STR_PAD_LEFT);
            $certificate->save();
        }

        $metadata = $certificate->metadata;
        
        $data = [
            'name' => $metadata['student_name'] ?? $certificate->name,
            'course_name' => $metadata['course_title'] ?? optional($certificate->course)->title ?? 'Course Title',
            'date' => Carbon::parse($metadata['completion_date'] ?? $certificate->created_at)->format('d M, Y'),
            'certificate_id' => $certificate->certificate_id,
            'qr' => base64_encode(QrCode::size(150)->format('svg')->margin(1)->generate(url("/certificate-verification?validation_hash=" . trim($certificate->validation_hash)))),
        ];

        $pdf = PDF::loadView('certificate.index', compact('data'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download("Certificate-{$certificate->certificate_id}.pdf");
    }


    /**
     * Get Verify Certificate form
     */
    public function getVerificationForm(Request $request)
    {
        session()->forget('data');
        if ($request->certificate_id) {
            $certificates = Certificate::where('id', '=', $request->certificate_id)->get();
            $data['certificates'] = $certificates;
            $data['certificate_id'] = $request->certificate_id;
            session(["data" => $data]);
        } elseif ($request->name && $request->date) {
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


    public function verifyCertificate(Request $request)
    {
        if ($request->certificate_id) {
            $certificates = Certificate::where('id', '=', $request->certificate_id)->get();
            $data['certificates'] = $certificates;
            $data['certificate_id'] = $request->certificate_id;
        } else {
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
        }

        session()->forget('certificates');
        return back()->with(['data' => $data]);
    }
}
