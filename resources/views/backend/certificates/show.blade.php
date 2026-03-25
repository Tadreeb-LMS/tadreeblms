@extends('backend.layouts.app')

@section('title', __('labels.backend.certificates.title') . ' | ' . app_name())

@section('content')
    <div class="pb-3 userheading d-flex justify-content-between align-items-center">
        <h4><span>@lang('labels.backend.certificates.title')</span></h4>
        <a href="{{ route('admin.certificates.manage.index') }}" class="btn btn-secondary btn-sm">
            @lang('labels.backend.certificates.back_to_list')
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">@lang('labels.backend.certificates.fields.preview')</div>
                <div class="card-body" style="min-height: 720px;">
                    <iframe src="{{ $previewUrl }}" width="100%" height="680" frameborder="0"></iframe>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">@lang('labels.backend.certificates.fields.details')</div>
                <div class="card-body">
                    <p><strong>@lang('labels.backend.certificates.fields.certificate_id'):</strong> {{ $certificate->certificate_id ?: ('#' . $certificate->id) }}</p>
                    <p><strong>@lang('labels.backend.certificates.fields.status'):</strong> {{ $certificate->status_label }}</p>
                    <p><strong>@lang('labels.backend.certificates.fields.issue_date'):</strong> {{ optional($certificate->created_at)->format('d M, Y H:i') }}</p>
                    @if($certificate->revoked_at)
                        <p><strong>@lang('labels.backend.certificates.fields.revoked_at'):</strong> {{ $certificate->revoked_at->format('d M, Y H:i') }}</p>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">@lang('labels.backend.certificates.fields.user_details')</div>
                <div class="card-body">
                    <p><strong>@lang('labels.backend.certificates.fields.user_name'):</strong> {{ optional($certificate->user)->full_name ?: '-' }}</p>
                    <p><strong>@lang('labels.backend.certificates.fields.user_email'):</strong> {{ optional($certificate->user)->email ?: '-' }}</p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">@lang('labels.backend.certificates.fields.course_details')</div>
                <div class="card-body">
                    <p><strong>@lang('labels.backend.certificates.fields.course_name'):</strong> {{ optional($certificate->course)->title ?: '-' }}</p>
                </div>
            </div>

            @if(auth()->user()->hasRole(config('access.users.admin_role')) || auth()->user()->can('certificate_reissue'))
                <div class="card mb-3">
                    <div class="card-header">@lang('labels.backend.certificates.reissue')</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.certificates.manage.reissue', $certificate->id) }}">
                            @csrf
                            <div class="form-group">
                                <label for="notes">@lang('labels.backend.certificates.fields.notes')</label>
                                <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="@lang('labels.backend.certificates.fields.reissue_notes_placeholder')"></textarea>
                            </div>
                            <div class="form-check mb-2">
                                <input type="hidden" name="notify_user" value="0">
                                <input type="checkbox" class="form-check-input" id="notify_user" name="notify_user" value="1">
                                <label class="form-check-label" for="notify_user">@lang('labels.backend.certificates.fields.notify_user')</label>
                            </div>
                            <button type="submit" class="btn btn-warning" onclick="return confirm('@lang('labels.backend.certificates.confirm_reissue')')">
                                @lang('labels.backend.certificates.reissue')
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">@lang('labels.backend.certificates.fields.issue_history')</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>@lang('labels.backend.certificates.fields.action')</th>
                            <th>@lang('labels.backend.certificates.fields.notes')</th>
                            <th>@lang('labels.backend.certificates.fields.performed_by')</th>
                            <th>@lang('labels.backend.certificates.fields.performed_at')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($certificate->histories as $history)
                            <tr>
                                <td>{{ ucfirst($history->action) }}</td>
                                <td>{{ $history->notes ?: '-' }}</td>
                                <td>{{ optional($history->actor)->full_name ?: '-' }}</td>
                                <td>{{ optional($history->created_at)->format('d M, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">@lang('labels.backend.certificates.fields.no_history')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
