@inject('request', 'Illuminate\Http\Request')
@extends('backend.layouts.app')

@section('title', 'Subscription'.' | '.app_name())
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css">

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
</style>
@endpush

@section('content')

<div class="userheading">
    <h4><span>@lang('Calender')</span></h4>
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

        <div id="calendar"></div>

        <!-- Add Schedule Modal -->
        <div class="modal fade" id="schedule-add">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h4 class="modal-title">Add Your Schedule</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <form method="POST" action="#">
                            @csrf
                            <div class="form-group">
                                <label>Schedule Name:</label>
                                <input type="text" class="form-control" name="schedule_name">
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success">Add Your Schedule</button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Edit Schedule Modal -->
        <div class="modal fade" id="schedule-edit">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h4 class="modal-title">Edit Your Schedule</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <form method="POST" action="#">
                            @csrf
                            <div class="form-group">
                                <label>Schedule Name:</label>
                                <input type="text" class="form-control" name="schedule_name">
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success">Save Your Schedule</button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Add Event Modal -->
        <div class="modal fade" id="event-add">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h4 class="modal-title">Add Event</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">

                        <form method="POST" action="{{ route('user.add-event') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label>Title:</label>
                                <input type="text" name="title" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>Content:</label>
                                <input type="text" name="content" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>Event Date:</label>
                                <input type="text" name="event_date" class="form-control" id="event_date">
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success">Save Event</button>
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

<script>

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          /*
          events: [
                {
                'title'  : 'event1',
                'start'  : '2022-04-01'
                },

            ]
            */

            //events: {!! $lessons !!},

            eventClick: function(info) {
                info.jsEvent.preventDefault(); // don't let the browser navigate

                if (info.event.url) {
                window.open(info.event.url);
                }
            },

            dateClick: function(info) {
                $('#event-add').modal('toggle');

                $("#event_date").val(info.dateStr);
                // alert('Clicked on: ' + info.dateStr);
                // alert('Coordinates: ' + info.jsEvent.pageX + ',' + info.jsEvent.pageY);
                // alert('Current view: ' + info.view.type);
                // change the day's background color just for fun
                info.dayEl.style.backgroundColor = 'red';
            },
            /*
            eventClick: function(event) {
                event.jsEvent.preventDefault();
                var modal = $("#schedule-edit");
                modal.modal();
            },
            */
           eventSources: [
               {
                   events: {!! $lessons !!},

                   eventClassNames : 'myclassname',
                    //color: 'grey',   // an option!
                    //textColor: 'black' // an option!
                }
            ]
        });



        calendar.render();


    });



    </script>
@endpush