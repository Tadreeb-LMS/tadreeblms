<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CertificateVerificationController extends Controller
{
    /**
     * Verify a certificate using its validation hash.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function verify(Request $request)
    {
        $hash = $request->query('validation_hash');

        if (!$hash) {
            return view('frontend.certificate-verification', [
                'status' => 'invalid',
                'message' => 'No validation hash provided.'
            ]);
        }

        $certificate = Certificate::with('course')->where('validation_hash', $hash)->first();

        if (!$certificate) {
            return view('frontend.certificate-verification', [
                'status' => 'invalid',
                'message' => 'Invalid or Counterfeit Certificate. This certificate record does not exist in our secure registry.'
            ]);
        }

        if ($certificate->isRevoked()) {
            return view('frontend.certificate-verification', [
                'status' => 'revoked',
                'certificate' => $certificate,
                'revoked_at' => $certificate->revoked_at->format('d M, Y'),
                'message' => 'This certificate was revoked on ' . $certificate->revoked_at->format('d M, Y') . '. It is no longer a valid credential.'
            ]);
        }

        // Extract immutable snapshot from metadata
        $metadata = $certificate->metadata;

        return view('frontend.certificates.verify', [
            'status' => 'verified',
            'certificate' => $certificate,
            'student_name' => $metadata['student_name'] ?? $certificate->name,
            'course_title' => $metadata['course_title'] ?? optional($certificate->course)->title ?? 'Course Title',
            'completion_date' => Carbon::parse($metadata['completion_date'] ?? $certificate->created_at)->format('d M, Y'),
            'certificate_id' => $certificate->certificate_id,
        ]);
    }
}
