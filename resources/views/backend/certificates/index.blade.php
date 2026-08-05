@extends('backend.layouts.app')

@section('title', __('labels.backend.certificates.title') . ' | ' . app_name())

@section('content')
@push('after-styles')
<style>
    .certificate-download-link{
        display:inline-flex;
        align-items:center;
        gap:8px;
        color: #000 !important;
        padding:7px 16px;
        border-radius:50px;
        text-decoration:none !important;
        font-size:13px;
        font-weight:600;
        transition:.25s;
        background: rgba(255, 255, 255, 0.35);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        box-shadow:
            0 2px 8px rgba(0, 0, 0, 0.08),
            inset 0 1px 0 rgba(255,255,255,.5);
    }
    .certificate-download-link:hover,
    .certificate-download-link:focus {
        background-color: #0b5ed7 !important;
        color: #fff !important;
        text-decoration: none !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13,110,253,.3);
    }
    .certificate-download-link:hover i,
    .certificate-download-link:focus i {
        color: #fff !important;
    }
</style>
@endpush
<div class="pb-3 userheading">
    <h4 class=""> <span>  @lang('labels.backend.certificates.title')</span></h4>
</div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">

                        <table id="myTable" class="table custom-teacher-table table-striped ">
                            <thead>
                                <tr>
                                    <th>@lang('labels.general.sr_no')</th>
                                    <th>@lang('labels.backend.certificates.fields.course_name')</th>
                                    <th>@lang('labels.backend.certificates.fields.Download-Link')</th>
                                </tr>
                            </thead>

                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@push('after-scripts')
<script>
$(document).ready(function() {
    $('#myTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("admin.certificates.get") }}', 
        select: { info: false }, // 👈 hides the “0 rows selected” message
        iDisplayLength: 10,

        language: {
            @if(app()->getLocale() == 'ar')
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
            @else
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/en-GB.json'
            @endif
        },

        columns: [
            { data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false },
            { data: "title", name: 'title' },
            { data: "link", name: 'link' },
        ]
    });
});
</script>
@endpush


