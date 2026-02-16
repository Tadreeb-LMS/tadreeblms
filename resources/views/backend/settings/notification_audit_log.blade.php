@extends('backend.layouts.app')
@section('title', __('labels.notifications.audit_log.title').' | '.app_name())

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">
                    <i class="fas fa-history mr-2"></i>{{ __('labels.notifications.audit_log.title') }}
                </h4>
            </div>
            <div class="col-sm-7 text-right">
                <a href="{{ route('admin.notification-settings') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> {{ __('labels.notifications.audit_log.back_to_settings') }}
                </a>
            </div>
        </div>

        <hr/>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>{{ __('labels.notifications.audit_log.date_time') }}</th>
                        <th>{{ __('labels.notifications.audit_log.user') }}</th>
                        <th>{{ __('labels.notifications.audit_log.action') }}</th>
                        <th>{{ __('labels.notifications.audit_log.module') }}</th>
                        <th>{{ __('labels.notifications.audit_log.event') }}</th>
                        <th>{{ __('labels.notifications.audit_log.channel') }}</th>
                        <th>{{ __('labels.notifications.audit_log.old_value') }}</th>
                        <th>{{ __('labels.notifications.audit_log.new_value') }}</th>
                        <th>{{ __('labels.notifications.audit_log.ip_address') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>{{ $log->user->full_name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge badge-{{ strpos($log->action, 'enabled') !== false ? 'success' : 'warning' }}">
                                {{ $log->action_label }}
                            </span>
                        </td>
                        <td>{{ ucfirst($log->module ?? '-') }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($log->event ?? '-')) }}</td>
                        <td>{{ ucfirst($log->channel ?? '-') }}</td>
                        <td>
                            @if($log->old_value !== null)
                                <span class="badge badge-{{ $log->old_value ? 'success' : 'secondary' }}">
                                    {{ $log->old_value ? __('labels.notifications.audit_log.enabled') : __('labels.notifications.audit_log.disabled') }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($log->new_value !== null)
                                <span class="badge badge-{{ $log->new_value ? 'success' : 'secondary' }}">
                                    {{ $log->new_value ? __('labels.notifications.audit_log.enabled') : __('labels.notifications.audit_log.disabled') }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">{{ __('labels.notifications.audit_log.no_logs_found') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
