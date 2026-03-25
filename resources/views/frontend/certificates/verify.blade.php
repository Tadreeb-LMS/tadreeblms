@extends('frontend.layouts.app' . config('theme_layout'))

@section('title', 'Certificate Verification | ' . app_name())

@push('after-styles')
<style>
    .verification-container {
        padding: 80px 0;
        min-height: 60vh;
        display: flex;
        align-items: center;
    }
    .verification-card {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        overflow: hidden;
        border: 1px solid #eee;
        transition: transform 0.3s ease;
    }
    .status-header {
        padding: 40px 20px;
        text-align: center;
        color: #fff;
    }
    .status-verified { background: #28a745; }
    .status-revoked { background: #dc3545; }
    .status-invalid { background: #6c757d; }
    
    .status-icon {
        font-size: 60px;
        margin-bottom: 15px;
    }
    .verification-details {
        padding: 40px;
    }
    .detail-item {
        margin-bottom: 25px;
        border-bottom: 1px solid #f8f9fa;
        padding-bottom: 10px;
    }
    .detail-label {
        font-size: 13px;
        text-transform: uppercase;
        color: #888;
        letter-spacing: 1px;
        margin-bottom: 5px;
    }
    .detail-value {
        font-size: 18px;
        color: #1a237e;
        font-weight: 600;
    }
    .download-btn {
        background: #c5a059;
        color: #fff;
        border: none;
        padding: 15px 30px;
        border-radius: 30px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-block;
        margin-top: 20px;
        transition: all 0.3s ease;
    }
    .download-btn:hover {
        background: #b38f4d;
        color: #fff;
        box-shadow: 0 5px 15px rgba(197, 160, 89, 0.4);
        transform: translateY(-2px);
    }
    .badge-premium {
        background: #1a237e;
        color: #fff;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
    }
</style>
@endpush

@section('content')
<div class="verification-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="verification-card">
                    @if($status === 'verified')
                        <div class="status-header status-verified">
                            <div class="status-icon"><i class="fas fa-check-circle"></i></div>
                            <h2 class="text-white mb-0">Authentic Certificate</h2>
                            <p class="text-white opacity-75 mt-2">Verified by TadreebLMS Registry</p>
                        </div>
                        <div class="verification-details text-center">
                            <div class="detail-item">
                                <div class="detail-label">Certified Professional</div>
                                <div class="detail-value">{{ $student_name }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Course Title</div>
                                <div class="detail-value">{{ $course_title }}</div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="detail-item">
                                        <div class="detail-label">Completion Date</div>
                                        <div class="detail-value">{{ $completion_date }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="detail-item">
                                        <div class="detail-label">Certificate ID</div>
                                        <div class="detail-value">{{ $certificate_id }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <a href="{{ route('admin.certificates.generate', ['course_id' => $certificate->course_id, 'user_id' => $certificate->user_id]) }}" class="download-btn">
                                <i class="fas fa-download mr-2"></i> Download Original PDF
                            </a>
                        </div>
                    @elseif($status === 'revoked')
                        <div class="status-header status-revoked">
                            <div class="status-icon"><i class="fas fa-exclamation-triangle"></i></div>
                            <h2 class="text-white mb-0">Certificate Revoked</h2>
                            <p class="text-white opacity-75 mt-2">Credential Nullified</p>
                        </div>
                        <div class="verification-details text-center">
                            <h4 class="text-danger mb-4">Caution</h4>
                            <p class="mb-4">{{ $message }}</p>
                            <div class="detail-item border-0">
                                <div class="detail-label">Revocation Date</div>
                                <div class="detail-value text-danger">{{ $revoked_at }}</div>
                            </div>
                        </div>
                    @else
                        <div class="status-header status-invalid">
                            <div class="status-icon"><i class="fas fa-times-circle"></i></div>
                            <h2 class="text-white mb-0">Invalid Credential</h2>
                        </div>
                        <div class="verification-details text-center">
                            <p class="lead mb-4">{{ $message }}</p>
                            <div class="alert alert-warning">
                                <strong>Warning:</strong> This hash does not match any record in our system. This may indicate a counterfeit certificate.
                            </div>
                            <a href="{{ route('frontend.index') }}" class="btn btn-outline-secondary mt-3">
                                Return to Homepage
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
