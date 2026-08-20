@extends('backend.layouts.app')
@section('title', __('labels.notifications.settings.title').' | '.app_name())

@push('after-styles')
<link rel="stylesheet" href="{{ asset('assets/css/colors/switch.css') }}">
<style>
    .form-control-label {
        line-height: 35px;
    }
    .switch.switch-3d {
        margin-bottom: 0px;
        vertical-align: middle;
    }
    .notification-setting-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 0;
        border-bottom: 1px solid #f1f1f1;
        gap: 20px;
    }

    .notification-setting-row:last-child {
        border-bottom: none;
    }

    .notification-label {
        font-size: 15px;
        font-weight: 500;
        color: #2d3748;
        margin-bottom: 0;
        flex: 1;
    }

    .toggle-group {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        min-width: 60px;
    }

    .switch.switch-3d {
        margin-bottom: 0 !important;
    }
    .card {
        margin-bottom: 20px;
    }
    .switch-label{
        width: 100%;
    }
</style>
@endpush

@section('content')

{{-- Alert Container --}}
<div id="notificationAlertContainer"></div>

{{-- Global Channel Controls --}}
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">
                    <i class="fas fa-sliders-h mr-2"></i>{{ __('labels.notifications.settings.global_channel_controls') }}
                </h4>
            </div>
            <div class="col-sm-7 text-right">
                <a href="{{ route('admin.notification-settings.audit-log') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-history mr-1"></i> {{ __('labels.notifications.settings.view_audit_log') }}
                </a>
            </div>
        </div>

        <hr/>
               <div class="notification-setting-row">
                    <label class="notification-label">
                        {{ __('labels.notifications.settings.email_notifications') }}
                    </label>

                    <div class="toggle-group">
                        <label class="switch switch-3d switch-primary">
                            <input
                                type="checkbox"
                                class="switch-input channel-master-toggle"
                                data-channel="email"
                                checked>

                            <span class="switch-label"></span>
                            <span class="switch-handle"></span>
                        </label>
                    </div>
                </div>
    </div>
</div>

@foreach($settings as $moduleKey => $module)
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-5">
                <h4 class="card-title mb-0">
                    <i class="{{ $module['icon'] }} mr-2"></i>{{ $module['label'] }}
                </h4>
            </div>
        </div>

        <hr/>

               @foreach($module['events'] as $eventKey => $event)

                <div class="notification-setting-row">

                    <label class="notification-label">
                        {{ $event['label'] }}
                    </label>

                    <div class="toggle-group">

                        @php
                            $emailChannel = $event['channels']['email'] ?? null;
                        @endphp

                        @if($emailChannel)

                        <label class="switch switch-3d switch-primary">

                            <input
                                type="checkbox"
                                class="switch-input notification-toggle"
                                data-module="{{ $moduleKey }}"
                                data-event="{{ $eventKey }}"
                                data-channel="email"
                                {{ $emailChannel['enabled'] ? 'checked' : '' }}>

                            <span class="switch-label"></span>
                            <span class="switch-handle"></span>

                        </label>

                        @endif

                    </div>

                </div>

                @endforeach
    </div>
</div>
@endforeach

@endsection

@push('after-scripts')
<script>
$(document).ready(function() {
    function showAlert(type, message) {
        var alertClass = (type === 'success') ? 'alert-success' : 'alert-danger';
        var iconClass = (type === 'success') ? 'fa-check-circle' : 'fa-exclamation-triangle';
        var html = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
            '<i class="fas ' + iconClass + ' mr-2"></i>' + message +
            '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>' +
            '</div>';
        $('#notificationAlertContainer').html(html);
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    }

    // Individual toggle
    $('.notification-toggle').on('change', function() {
        var $this = $(this);
        var module = $this.data('module');
        var event = $this.data('event');
        var channel = $this.data('channel');
        var enabled = $this.is(':checked') ? 1 : 0;

        $.ajax({
            url: '{{ route("admin.notification-settings.update") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                module: module,
                event: event,
                channel: channel,
                enabled: enabled
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                } else {
                    showAlert('danger', response.message);
                    $this.prop('checked', !enabled);
                }
            },
            error: function(xhr) {
                showAlert('danger', '{{ __("labels.notifications.settings.failed_to_update") }}');
                $this.prop('checked', !enabled);
            }
        });
    });

    // Channel master toggle
    $('.channel-master-toggle').on('change', function() {
        var $this = $(this);
        var channel = $this.data('channel');
        var enabled = $this.is(':checked') ? 1 : 0;

        $.ajax({
            url: '{{ route("admin.notification-settings.bulk-channel") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                channel: channel,
                enabled: enabled
            },
            success: function(response) {
                if (response.success) {
                    // Update all toggles for this channel
                    $('.notification-toggle[data-channel="' + channel + '"]').prop('checked', enabled);
                    showAlert('success', response.message);
                } else {
                    showAlert('danger', response.message);
                    $this.prop('checked', !enabled);
                }
            },
            error: function(xhr) {
                showAlert('danger', '{{ __("labels.notifications.settings.failed_to_update_channel") }}');
                $this.prop('checked', !enabled);
            }
        });
    });
});
</script>
@endpush
