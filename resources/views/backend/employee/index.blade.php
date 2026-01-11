@extends('backend.layouts.app')
@section('title', 'Employee' . ' | ' . app_name())
@push('after-styles')
    <link rel="stylesheet" href="{{ asset('assets/css/colors/switch.css') }}">
       <style>
          .switch.switch-3d.switch-lg {
    width: 40px;
    height: 20px;
}
.switch.switch-3d.switch-lg .switch-handle {
    width: 20px;
    height: 20px;
}

 .dropdown-menu {
        min-width: 160px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        padding: 5px;
    }

    .custom-dropdown-item {
        font-size: 14px;
        padding: 8px 16px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background-color 0.2s ease;
    }

    .custom-dropdown-item:hover {
        background-color: #f0f0f0;
        color: #000;
    }

    .custom-dropdown-item i {
        font-size: 14px;
    }
    </style>
@endpush
@section('content')

<div>
    <div class="d-flex justify-content-between pb-3">
        <div class="grow">
            <h4 class="text-20">@lang('Trainee')</h4>
        </div>

        @can('trainee_create')
        <div>
            <a href="{{ route('admin.employee.create') }}">
                <button type="button" class="add-btn">
                    @lang('strings.backend.general.app_add_new')
                </button>
            </a>
        </div>
        @endcan
    </div>

    <div class="card" style="border: none;">
        <div class="card-body">
            <div class="row">

                <!-- IMPORT FORM -->
                <div class="col-lg-6 col-sm-12 mb-4">
                    <h6>@lang('Import Department')</h6>

                    <form method="POST" action="{{ route('admin.employee.import') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="d-flex">

                            <div class="custom-file-upload-wrapper" style="margin-top: 18px;">
                                <input type="file" name="import_file" id="importFileInput" class="custom-file-input">
                                <label for="importFileInput" class="custom-file-label">
                                    <i class="fa fa-upload mr-1"></i> Choose a file
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary ml-3" name="submit" value="submit">
                                @lang('Import')
                            </button>
                        </div>
                    </form>
                </div>

            </div>

            <!-- FILTER SECTION -->
            <div class="d-block mt-2">
                <ul class="list-inline">
                    <li class="list-inline-item">
                        <a href="{{ route('admin.employee.index') }}"
                           style="{{ request('show_deleted') == 1 ? '' : 'font-weight: 700' }}">
                            {{ trans('labels.general.all') }}
                        </a>
                    </li>
                    |
                    <li class="list-inline-item">
                        <a href="{{ route('admin.employee.index') }}?show_deleted=1"
                           style="{{ request('show_deleted') == 1 ? 'font-weight: 700' : '' }}">
                            {{ trans('labels.general.trash') }}
                        </a>
                    </li>
                </ul>
            </div>

            <!-- TABLE -->
            <table id="myTable" class="custom-teacher-table table-striped" style="width: 1550px;">
                <thead>
                    <tr>
                        @can('category_delete')
                            @if (request('show_deleted') != 1)
                                <th style="text-align:center;">
                                    <input type="checkbox" class="mass" id="select-all" />
                                </th>
                            @endif
                        @endcan

                        <th>@lang('SL NO')</th>
                        <th>@lang('Employee Id')</th>
                        <th>@lang('labels.backend.teachers.fields.first_name')</th>
                        <th>@lang('labels.backend.teachers.fields.last_name')</th>
                        <th>@lang('labels.backend.teachers.fields.email')</th>
                        <th>@lang('Department')</th>
                        <th>@lang('Position')</th>
                        <th>@lang('labels.backend.teachers.fields.status')</th>

                        <th style="text-align:center;">@lang('strings.backend.general.actions')</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- RESET PASSWORD MODAL -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">

        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Reset password</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form action="" id="password-reset-form">
                        <h4>Are you sure you want to reset the password for <span class="email"></span>?</h4>

                        <div class="form-group m-0">
                            <button type="submit" class="btn btn-primary">Send Reset Link</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

   

@endsection

@push('after-scripts')
    <script>
        $(document).ready(function() {



            var route = '{{ route('admin.employee.get_data', ['status' => $status]) }}';

            @if (request('show_deleted') == 1)
                route = '{{ route('admin.employee.get_data', ['show_deleted' => 1]) }}';
            @endif

            var table = $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                iDisplayLength: 10,
                retrieve: true,
                dom: "<'table-controls'lfB>" +
                     "<'table-responsive't>" +
                     "<'d-flex justify-content-between align-items-center mt-3'ip><'actions'>",
                       buttons: [
    {
        extend: 'collection',
        text: '<i class="fa fa-download icon-styles"></i>',
        className: '',
        buttons: [
            {
                extend: 'csv',
                text: 'CSV',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5]
                }
            },
            {
                extend: 'pdf',
                text: 'PDF',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5]
                }
            }
        ]
    },
      {extend: 'colvis',
    text: '<i class="fa fa-eye icon-styles" aria-hidden="" ></i>',
    },
],
                
                ajax: route,
                columns: [
                    
                    // {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable:false},
                    {
                        data: "id",
                        name: 'id'
                    },
                    {
                        data: "emp_id",
                        name: 'emp_id'
                    },
                    {
                        data: "first_name",
                        name: 'first_name'
                    },
                    {
                        data: "last_name",
                        name: 'last_name'
                    },
                    {
                        data: "email",
                        name: 'email'
                    },
                    {
                        data: "department",
                        name: 'department'
                    },
                    {
                        data: "position",
                        name: 'position'
                    },
                    //{data: "qr_code", name: 'qr_code'},
                    {
                        data: "status",
                        name: 'status'
                    },
                    {
                        data: "actions",
                        name: 'actions'
                    }
                ],
                @if (request('show_deleted') != 1)
                    columnDefs: [{
                            "width": "5%",
                            "targets": 0
                        },
                        {
                            "className": "text-center",
                            "targets": [0]
                        }
                    ],
                @endif
                 initComplete: function () {
                      let $searchInput = $('#myTable_filter input[type="search"]');
    $searchInput
        .addClass('custom-search')
        .wrap('<div class="search-wrapper position-relative d-inline-block"></div>')
        .after('<i class="fa fa-search search-icon"></i>');

    $('#myTable_length select').addClass('form-select form-select-sm custom-entries');
                },

                createdRow: function(row, data, dataIndex) {
                    $(row).attr('data-entry-id', data.id);
                },
                language: {
                    url: "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{{ $locale_full_name }}.json",
                    buttons: {
                        colvis: '{{ trans('datatable.colvis') }}',
                        pdf: '{{ trans('datatable.pdf') }}',
                        csv: '{{ trans('datatable.csv') }}',
                    },
                    search:"",
    //                              paginate: {
    //     previous: '<i class="fa fa-angle-left"></i>',
    //     next: '<i class="fa fa-angle-right"></i>'
    // },
                }

            });
            @if (auth()->user()->isAdmin())
                // $('.actions').html('<a href="' + '{{ route('admin.teachers.mass_destroy') }}' +
                //     '" class="btn btn-xs btn-danger js-delete-selected" style="margin-top:0.755em;margin-left: 20px;">Delete selected</a>'
                // );
            @endif




            $(document).on('click', '.switch-input', function(e) {
                var id = $(this).data('id');
                $.ajax({
                    type: "POST",
                    url: "{{ route('admin.employee.status') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                    },
                }).done(function() {
                    var table = $('#myTable').DataTable();
                    table.ajax.reload();
                });
            })

            $(document).on('click', '.send-reset-password-link', function(e) {
                e.preventDefault();
                const link = $(this).attr('href');
                const email = $(this).attr('data-email');

                $('#resetPasswordModal form').attr('action', link);
                $('#resetPasswordModal .email').text(email);

                $('#resetPasswordModal').modal('show');

            });
            $(document).on('submit', '#password-reset-form', function(e) {
                e.preventDefault();
                $('#password-reset-form').attr('action')
                $.ajax({
                    type: "get",
                    url: $('#password-reset-form').attr('action'),
                    success: function(response) {
                        alert('Password reset link sent successfully');
                        $('#resetPasswordModal').modal('hide');
                    }
                });
            });

        });
    </script>
    <script>
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            const label = input.nextElementSibling;
            const fileName = e.target.files.length > 0 ? e.target.files[0].name : 'Choose a file';
            label.innerHTML = '<i class="fa fa-upload mr-1"></i> ' + fileName;
        });
    });
</script>
@endpush
