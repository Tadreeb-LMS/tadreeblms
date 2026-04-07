@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')

@section('title', __('menus.backend.sidebar.calendar') . ' | ' . app_name())
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css">
<link rel="stylesheet" href="{{ asset('frontend/css/calendar.css') }}">

@push('after-styles')
<style>
    .userheading .btn-primary, 
    .modal-footer .btn-success {
        background: linear-gradient(45deg, #233e74 0%, #c1902d 100%) !important;
        border: none !important;
        color: #fff !important;
        transition: all 0.3s ease !important;
    }

    .userheading .btn-primary:hover,
    .modal-footer .btn-success:hover {
        background: linear-gradient(45deg, #c1902d 0%, #233e74 100%) !important;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        color: #fff !important;
    }

    .calendar-legend {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        align-items: center;
        padding: 10px 0;
    }
    .calendar-legend .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #555;
    }
    .calendar-legend .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }
</style>
@endpush

@section('content')
@php
    $calendarLocale = app()->getLocale();
    $calendarDirection = $calendarLocale === 'ar' ? 'rtl' : 'ltr';
@endphp

<div class="userheading">
    <h4><span>{{ __('menus.backend.sidebar.calendar') }}</span></h4>
</div>

<div class="d-flex justify-content-between pb-3 align-items-center userheading">
    @can('course_create')
        <div>
            <a href="{{ route('admin.courses.create') }}" 
               class="btn btn-primary">
                @lang('strings.backend.general.app_add_new_course')
            </a>
        </div>
    @endcan
</div>

<div class="card" style="border-radius: 5px;">
    <div class="card-body">

        <div class="calendar-legend">
            <span class="legend-item"><span class="legend-dot" style="background:#6c757d;"></span> {{ __('calendar_page.legend_lessons') }}</span>
            <span class="legend-item"><span class="legend-dot" style="background:#4285F4;"></span> {{ __('calendar_page.legend_live_sessions') }}</span>
            <span class="legend-item"><span class="legend-dot" style="background:#34A853;"></span> {{ __('calendar_page.legend_live_lesson_slots') }}</span>
            <span class="legend-item"><span class="legend-dot" style="background:#E91E63;"></span> {{ __('calendar_page.legend_scheduled_sessions') }}</span>
        </div>

        <div id="calendar"></div>

        <!-- Add Schedule Modal -->
        <div class="modal fade" id="schedule-add">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h4 class="modal-title">{{ __('calendar_page.add_schedule') }}</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <form method="POST" action="#">
                            @csrf
                            <div class="form-group">
                                <label>{{ __('calendar_page.schedule_name') }}</label>
                                <input type="text" class="form-control" name="schedule_name">
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('calendar_page.close') }}</button>
                        <button type="button" class="btn btn-success">{{ __('calendar_page.add_schedule') }}</button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Edit Schedule Modal -->
        <div class="modal fade" id="schedule-edit">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h4 class="modal-title">{{ __('calendar_page.edit_schedule') }}</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <form method="POST" action="#">
                            @csrf
                            <div class="form-group">
                                <label>{{ __('calendar_page.schedule_name') }}</label>
                                <input type="text" class="form-control" name="schedule_name">
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('calendar_page.close') }}</button>
                        <button type="button" class="btn btn-success">{{ __('calendar_page.save_schedule') }}</button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Add Event Modal -->
        <div class="modal fade" id="event-add">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h4 class="modal-title">{{ __('calendar_page.add_event') }}</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">

                        <form method="POST" action="{{ route('user.add-event') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label>{{ __('calendar_page.event_title') }}</label>
                                <input type="text" name="title" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>{{ __('calendar_page.event_content') }}</label>
                                <input type="text" name="content" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>{{ __('calendar_page.event_date') }}</label>
                                <input type="date" name="event_date" class="form-control" id="event_date" min="{{ date('Y-m-d') }}">
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">{{ __('calendar_page.close') }}</button>
                                <button type="submit" class="btn btn-success">{{ __('calendar_page.save_event') }}</button>
                            </div>

                        </form>

                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('after-scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/locales-all.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: @json($calendarLocale),
            direction: @json($calendarDirection),
            eventDisplay: 'block',
            dayMaxEventRows: 3,
            eventContent: function(arg) {
                var timeText = arg.timeText || '';
                var title = arg.event.title || '';
                var html = '';
                if (timeText) {
                    html += '<div class="fc-event-time-top">' + timeText + '</div>';
                }
                html += '<div class="fc-event-title-bottom">' + title + '</div>';
                return { html: html };
            },

            eventClick: function(info) {
                info.jsEvent.preventDefault();

                if (info.event.url) {
                    var newWindow = window.open(info.event.url, '_blank');
                    if (!newWindow || newWindow.closed || typeof newWindow.closed === 'undefined') {
                        window.location.href = info.event.url;
                    }
                }
            },

            dateClick: function(info) {
                let today = new Date().toISOString().split('T')[0];

                if (info.dateStr < today) {
                    alert(@json(__('calendar_page.cannot_create_past_events')));
                    return;
                }

                $('#event-add').modal('toggle');
                $("#event_date").val(info.dateStr);
            },

            eventSources: [
                {
                    events: {!! $lessons !!},
                    color: '#6c757d',
                    textColor: '#fff',
                },
                {
                    events: {!! $liveSessions !!},
                    color: '#4285F4',
                    textColor: '#fff',
                },
                {
                    events: {!! $liveLessonSlots !!},
                    color: '#34A853',
                    textColor: '#fff',
                },
                {
                    events: {!! $scheduledSessions !!},
                    color: '#E91E63',
                    textColor: '#fff',
                }
            ]
        });

        calendar.render();
    });
</script>
@endpush