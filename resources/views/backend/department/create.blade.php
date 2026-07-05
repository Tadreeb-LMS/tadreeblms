@extends('backend.layouts.app')
@section('title', __('labels.backend.pages.title').' | '.app_name())

@push('after-styles')
    <link rel="stylesheet" type="text/css" href="{{asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.css')}}">
    <style>
        .select2-container--default .select2-selection--single {
            height: 35px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 35px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 35px;
        }
        .bootstrap-tagsinput{
            width: 100%!important;
            display: inline-block;
        }
        .bootstrap-tagsinput .tag{
            line-height: 1;
            margin-right: 2px;
            background-color: #2f353a ;
            color: white;
            padding: 3px;
            border-radius: 3px;
        }

    </style>
@endpush

@section('content')

    <form id="add-dep" action="{{ route('admin.department.store') }}" method="post" enctype="multipart/form-data" novalidate>
         @csrf()

         <div class="pb-3 d-flex justify-content-between align-items-center">
         <h4 >
        Create User Group
     </h4>
    
         <div>
              <a href="{{ route('admin.department.index') }}"
             class="btn add-btn">View User Group</a>

         </div>
     
 </div>
        <div class="card">
            <!-- <div class="card-header">
                <h3 class="page-title float-left mb-0">Create Department</h3>
                <div class="float-right">
                    <a href="{{ route('admin.department.index') }}"
                    class="btn btn-success">View Department</a>
                </div>
            </div> -->

            <div class="card-body">
                <div class="row">
                    <div class="col-12 form-group">
                        <label for="title" class="control-label">
                            Title <span class="text-danger">*</span>
                        </label>
                        <input value="{{ old('title') }}"
                               class="form-control @error('title') is-invalid @enderror"
                               placeholder="Title"
                               name="title"
                               type="text"
                               id="title"
                               required
                               aria-required="true"
                               aria-describedby="title-error">
                        <div id="title-error" class="invalid-feedback" data-required-message="The title field is required.">
                            {{ $errors->first('title', 'The title field is required.') }}
                        </div>
                    </div>

                </div>
                

                <div class="row">
                    <div class="col-12 form-group">
                        <label for="content" class="control-label">Description</label>
                        <textarea class="form-control" placeholder="Enter description" name="content" rows="4">{{ old('content') }}</textarea>

                    </div>
                </div>


                <div class="row">
                    <div class="col-md-12 d-flex justify-content-between">
                        <button type="reset" class="btn cancel-btn waves-effect waves-light pl-5 pr-5 mr-3 ">
                        
                            {{trans('labels.backend.pages.fields.clear')}}
                        </button>
                        <button type="submit" class="btn add-btn waves-effect waves-light pl-5 pr-5 ">
                        {{trans('labels.general.buttons.save')}}
                        </button>
                    </div>

                </div>

            </div>
            <input type="hidden" id="feedback_index" value="{{ route('admin.department.index') }}">
        <input type="hidden" id="user-assisment" value="{{ url('user/assessment_accounts/new_assisment/create') }}">
        </div>
    </form>

@endsection

@push('after-scripts')
    <script src="{{asset('plugins/bootstrap-tagsinput/bootstrap-tagsinput.js')}}"></script>
    <script type="text/javascript" src="{{asset('/vendor/unisharp/laravel-ckeditor/ckeditor.js')}}"></script>
    <script type="text/javascript" src="{{asset('/vendor/unisharp/laravel-ckeditor/adapters/jquery.js')}}"></script>
    <script src="{{asset('/vendor/laravel-filemanager/js/lfm.js')}}"></script>
    <script>
        $('.editor').each(function () {

            CKEDITOR.replace($(this).attr('id'), {
                filebrowserImageBrowseUrl: '/laravel-filemanager?type=Images',
                filebrowserImageUploadUrl: '/laravel-filemanager/upload?type=Images&_token={{csrf_token()}}',
                filebrowserBrowseUrl: '/laravel-filemanager?type=Files',
                filebrowserUploadUrl: '/laravel-filemanager/upload?type=Files&_token={{csrf_token()}}',

                extraPlugins: 'smiley,lineutils,widget,codesnippet,prism,flash,colorbutton,colordialog,codesnippet',
            });

        });

        var uploadField = $('input[type="file"]');

        $(document).on('change','input[type="file"]',function () {
            var $this = $(this);
            $(this.files).each(function (key,value) {
                if((value.size/1024) > 10240){
                    alert('"'+value.name+'"'+'exceeds limit of maximum file upload size' )
                    $this.val("");
                }
            })
        })

    </script>

<script>
     var nxt_url_val= '';

    $('.frm_submit').on('click', function (){
        nxt_url_val = $(this).val();
    });
    $(document).on('input', '#add-dep [required]', function () {
        $(this).removeClass('is-invalid');
    });

    $(document).on('submit', '#add-dep', function (e) {
        e.preventDefault();

        var form = this;
        var $form = $(form);
        var hrefurl = $(location).attr("href");
        var last_part = hrefurl.substr(hrefurl.lastIndexOf('/') + 19);

        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback[data-required-message]').each(function () {
            $(this).text($(this).data('required-message'));
        });

        if (!form.checkValidity()) {
            $form.find(':invalid').addClass('is-invalid').first().focus();
            return;
        }

        // Sync CKEditor content back to textarea before serializing
        for (var instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }

        $.ajax({
            type: 'POST',
            url: $form.attr('action'),
            data: $form.serialize(),
            dataType: 'json',
            success: function () {
                if (last_part == 'add_dep') {
                    window.location.href = $("#user-assisment").val();
                    return;
                }

                window.location.href = $("#feedback_index").val();
            },
            error: function (xhr) {
                if (xhr.status !== 422 || !xhr.responseJSON || !xhr.responseJSON.errors) {
                    return;
                }

                $.each(xhr.responseJSON.errors, function (field, messages) {
                    var $field = $form.find('[name="' + field + '"]');
                    $field.addClass('is-invalid');
                    $field.siblings('.invalid-feedback').text(messages[0]);
                });

                $form.find('.is-invalid').first().focus();
            }
        });
    });
</script>
@endpush
