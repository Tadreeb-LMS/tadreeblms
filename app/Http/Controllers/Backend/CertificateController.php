<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\{Certificate, CertificateHistory, courseAssignment, UserCourseDetail};
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
     * Admin certificate management dashboard.
     */
    public function adminIndex()
    {
        abort_unless($this->hasCertificatePermission('certificate_access'), 403);

        return view('backend.certificates.admin_index', [
            'courses' => Course::query()->orderBy('title')->get(['id', 'title']),
            'users' => User::query()->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']),
            'statuses' => ['issued', 'revoked', 'reissued'],
        ]);
    }

    /**
     * Server-side datatable for issued certificates.
     */
    public function adminData(Request $request)
    {
        abort_unless($this->hasCertificatePermission('certificate_access'), 403);

        $query = Certificate::query()->with(['user:id,first_name,last_name,email', 'course:id,title']);

        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                if ($request->filled('course_id')) {
                    $query->where('course_id', (int) $request->course_id);
                }

                if ($request->filled('user_id')) {
                    $query->where('user_id', (int) $request->user_id);
                }

                if ($request->filled('from_date')) {
                    $query->whereDate('created_at', '>=', $request->from_date);
                }

                if ($request->filled('to_date')) {
                    $query->whereDate('created_at', '<=', $request->to_date);
                }

                if ($request->filled('status')) {
                    if ($request->status === 'revoked') {
                        $query->whereNotNull('revoked_at');
                    } elseif ($request->status === 'reissued') {
                        $query->whereNull('revoked_at')->where('status', Certificate::STATUS_REISSUED);
                    } elseif ($request->status === 'issued') {
                        $query->whereNull('revoked_at')->where('status', '!=', Certificate::STATUS_REISSUED);
                    }
                }

                $keyword = trim((string) $request->input('search.value', ''));
                if ($keyword !== '') {
                    $query->where(function ($inner) use ($keyword) {
                        $inner->where('certificate_id', 'like', "%{$keyword}%")
                            ->orWhereHas('user', function ($u) use ($keyword) {
                                $u->where('first_name', 'like', "%{$keyword}%")
                                    ->orWhere('last_name', 'like', "%{$keyword}%")
                                    ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) LIKE ?", ["%{$keyword}%"]);
                            })
                            ->orWhereHas('course', function ($c) use ($keyword) {
                                $c->where('title', 'like', "%{$keyword}%");
                            });
                    });
                }
            })
            ->addColumn('certificate_code', function ($row) {
                return $row->certificate_id ?: ('#' . $row->id);
            })
            ->addColumn('user_name', function ($row) {
                return optional($row->user)->full_name ?: '-';
            })
            ->addColumn('course_name', function ($row) {
                return optional($row->course)->title ?: '-';
            })
            ->addColumn('issue_date', function ($row) {
                return optional($row->created_at)->format('d M, Y');
            })
            ->addColumn('status_label', function ($row) {
                return $row->status_label;
            })
            ->addColumn('actions', function ($row) {
                $viewUrl = route('admin.certificates.manage.show', $row->id);
                $reissueUrl = route('admin.certificates.manage.reissue', $row->id);

                $viewAction = '<a href="' . $viewUrl . '" class="btn btn-sm btn-primary mr-1">' . trans('labels.backend.certificates.view') . '</a>';
                $reissueAction = '';

                if ($this->hasCertificatePermission('certificate_reissue')) {
                    $reissueAction = '<form method="POST" action="' . $reissueUrl . '" class="d-inline-block">'
                        . csrf_field()
                        . '<input type="hidden" name="notes" value="Reissued from certificate module">'
                        . '<button type="submit" class="btn btn-sm btn-warning" onclick="return confirm(\'' . trans('labels.backend.certificates.confirm_reissue') . '\')">' . trans('labels.backend.certificates.reissue') . '</button>'
                        . '</form>';
                }

                return $viewAction . $reissueAction;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Show a certificate details page for admins.
     */
    public function show($id)
    {
        abort_unless($this->hasCertificatePermission('certificate_view'), 403);

        $certificate = Certificate::with(['user', 'course', 'histories.actor'])->findOrFail($id);

        $previewUrl = route('admin.certificates.generate', [
            'course_id' => $certificate->course_id,
            'user_id' => $certificate->user_id,
        ]);

        return view('backend.certificates.show', compact('certificate', 'previewUrl'));
    }

    /**
     * Reissue a certificate and keep an audit trail.
     */
    public function reissue(Request $request, $id)
    {
        abort_unless($this->hasCertificatePermission('certificate_reissue'), 403);

        $request->validate([
            'notes' => 'nullable|string|max:500',
            'notify_user' => 'nullable|boolean',
        ]);

        $certificate = DB::transaction(function () use ($id, $request) {
            $certificate = Certificate::with(['user', 'course'])->lockForUpdate()->findOrFail($id);

            $oldCertificateId = $certificate->certificate_id;
            $nextVersion = $certificate->histories()->where('action', 'reissued')->count() + 1;
            $baseId = $oldCertificateId ?: ('TLMS-' . Carbon::now()->format('Y') . '-' . str_pad($certificate->id, 6, '0', STR_PAD_LEFT));
            $newCertificateId = $baseId . '-R' . $nextVersion;

            $certificate->validation_hash = hash('sha256', $certificate->user_id . $certificate->course_id . now() . config('app.key'));
            $certificate->certificate_id = $newCertificateId;
            $certificate->status = Certificate::STATUS_REISSUED;
            $certificate->revoked_at = null;

            $metadata = (array) $certificate->metadata;
            $metadata['previous_certificate_id'] = $oldCertificateId;
            $metadata['reissued_at'] = now()->toDateTimeString();
            $metadata['reissued_by'] = auth()->id();
            if ($request->filled('notes')) {
                $metadata['reissue_notes'] = $request->notes;
            }

            $certificate->metadata = $metadata;
            $certificate->save();

            CertificateHistory::create([
                'certificate_id' => $certificate->id,
                'action' => 'reissued',
                'notes' => $request->notes,
                'metadata' => [
                    'old_certificate_id' => $oldCertificateId,
                    'new_certificate_id' => $newCertificateId,
                    'reissued_by' => auth()->id(),
                ],
                'created_by' => auth()->id(),
            ]);

            return $certificate;
        });

        if ((bool) $request->input('notify_user', false) && $certificate->user && $certificate->course) {
            CertificateNotification::sendCertificateIssuedEmail($certificate->user, $certificate->course->title);
        }

        return redirect()->route('admin.certificates.manage.show', $certificate->id)
            ->withFlashSuccess(trans('alerts.backend.general.updated'));
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
        abort_unless(auth()->check(), 403);

        $user_id = $request->user_id ?? auth()->id();
        $course_id = $request->course_id;

        if ((int) auth()->id() !== (int) $user_id) {
            abort_unless(
                $this->hasCertificatePermission('certificate_view') || $this->hasCertificatePermission('certificate_access'),
                403
            );
        }

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
        abort_unless(auth()->check(), 403);

        $certificateId = $request->certificate_id;
        
        // Search by primary ID or the human-readable certificate_id
        $certificate = Certificate::with('course')->where('id', $certificateId)
            ->orWhere('certificate_id', $certificateId)
            ->firstOrFail();
        abort_unless($this->canAccessCertificate($certificate), 403);
        $this->ensureCertificateIdentity($certificate);

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

    /**
     * Backfill validation hash and human readable id for legacy records.
     */
    protected function ensureCertificateIdentity(Certificate $certificate)
    {
        $isDirty = false;

        if (!$certificate->validation_hash) {
            $certificate->validation_hash = hash('sha256', $certificate->user_id . $certificate->course_id . now() . config('app.key'));
            $isDirty = true;
        }

        if (!$certificate->certificate_id) {
            $certificate->certificate_id = 'TLMS-' . Carbon::now()->format('Y') . '-' . str_pad($certificate->id, 6, '0', STR_PAD_LEFT);
            $isDirty = true;
        }

        if ($isDirty) {
            $certificate->save();
        }
    }

    protected function hasCertificatePermission($permission)
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->hasRole(config('access.users.admin_role')) || $user->can($permission);
    }

    protected function canAccessCertificate(Certificate $certificate)
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if ((int) $user->id === (int) $certificate->user_id) {
            return true;
        }

        return $this->hasCertificatePermission('certificate_view') || $this->hasCertificatePermission('certificate_access');
    }
}
