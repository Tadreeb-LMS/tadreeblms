@extends('backend.layouts.app')

@section('title', __('labels.backend.certificates.title') . ' | ' . app_name())

@section('content')
    <div class="pb-3 userheading">
        <h4><span>@lang('labels.backend.certificates.title')</span></h4>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label>@lang('labels.backend.certificates.fields.course_name')</label>
                    <select id="filter_course" class="form-control">
                        <option value="">@lang('labels.backend.certificates.fields.all_courses')</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label>@lang('labels.backend.certificates.fields.user_name')</label>
                    <select id="filter_user" class="form-control">
                        <option value="">@lang('labels.backend.certificates.fields.all_users')</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->full_name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label>@lang('labels.backend.certificates.fields.status')</label>
                    <select id="filter_status" class="form-control">
                        <option value="">@lang('labels.backend.certificates.fields.all_statuses')</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label>@lang('labels.backend.certificates.fields.from_date')</label>
                    <input type="date" id="filter_from_date" class="form-control">
                </div>
                <div class="col-md-2 mb-2">
                    <label>@lang('labels.backend.certificates.fields.to_date')</label>
                    <input type="date" id="filter_to_date" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="certificateTable" class="table custom-teacher-table table-striped">
                    <thead>
                    <tr>
                        <th>@lang('labels.backend.certificates.fields.certificate_id')</th>
                        <th>@lang('labels.backend.certificates.fields.user_name')</th>
                        <th>@lang('labels.backend.certificates.fields.course_name')</th>
                        <th>@lang('labels.backend.certificates.fields.issue_date')</th>
                        <th>@lang('labels.backend.certificates.fields.status')</th>
                        <th>@lang('labels.backend.certificates.fields.action')</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('after-scripts')
    <script>
        $(document).ready(function() {
            var table = $('#certificateTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin.certificates.manage.data') }}',
                    data: function(d) {
                        d.course_id = $('#filter_course').val();
                        d.user_id = $('#filter_user').val();
                        d.status = $('#filter_status').val();
                        d.from_date = $('#filter_from_date').val();
                        d.to_date = $('#filter_to_date').val();
                    }
                },
                iDisplayLength: 10,
                language: {
                    @if (app()->getLocale() == 'ar')
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
                    @else
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/en-GB.json'
                    @endif
                },
                columns: [
                    {data: 'certificate_code', name: 'certificate_id'},
                    {data: 'user_name', name: 'user_name', orderable: false},
                    {data: 'course_name', name: 'course_name', orderable: false},
                    {data: 'issue_date', name: 'created_at'},
                    {data: 'status_label', name: 'status'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false}
                ]
            });

            $('#filter_course, #filter_user, #filter_status, #filter_from_date, #filter_to_date').on('change', function() {
                table.ajax.reload();
            });
        });
    </script>
@endpush
