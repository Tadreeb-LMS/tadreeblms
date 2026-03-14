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

    <!-- <form action="{{ route('admin.department.store') }}" method="post" enctype="multipart/form-data"> -->
    <form id="add-dep" method="post" enctype="multipart/form-data">
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
                        <label for="title" class="control-label">Title</label>
                        <input value="{{ old('title') }}" class="form-control" placeholder="Title" name="title" type="text" id="title">
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
    $(document).on('submit', '#add-dep', function (e) {
    e.preventDefault();
    hrefurl=$(location).attr("href");
    last_part=hrefurl.substr(hrefurl.lastIndexOf('/') + 19)
   // alert(last_part)
    setTimeout(() => {
        // Sync CKEditor content back to textarea before serializing
        for (var instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }
        let data = $('#add-dep').serialize();
        let url = '{{route('admin.department.store')}}';
        var redirect_url=$("#feedback_index").val();
        var redirect_url_course=$("#user-assisment").val();
            $.ajax({
                    type: 'POST',
                    url: url,
                    data: data,
                    datatype: "json",
                    success: function (res) {
                    console.log(res)
                    if(last_part == 'add_dep'){
                        window.location.href = redirect_url_course;
                        return;
                    }
                    else{
                        window.location.href = redirect_url;
                        return;
                    }
                }
                })
            }, 100);
        })
</script>
@endpush
