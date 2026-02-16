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
    
<form action="{{ route('admin.department.update',$page->id) }}" method="post" enctype="multipart/form-data">
    @csrf()  
    <div class="pb-3 d-flex justify-content-between align-items-between">
    <h4 >
        Edit Department
    </h4>
    <div class="">
        <a href="{{ route('admin.department.index') }}"
           class="btn add-btn">View Deaprtment</a>
    </div>
  
</div>
    <div class="card">
        <!-- <div class="card-header">
            <h3 class="page-title float-left mb-0">Edit Department</h3>
            <div class="float-right">
                <a href="{{ route('admin.department.index') }}"
                   class="btn btn-success">View Deaprtment</a>
            </div>
        </div> -->
        <div class="card-body">
            <div class="row">
                <div class="col-12 form-group">
                    
                    <label for="title" class="control-label">Title</label>
                    <input value="{{ $page->title }}" class="form-control" placeholder="Title" name="title" type="text" id="title">
                    
                </div>
            </div>


           



            <div class="row">


                <div class="col-md-12 d-flex justify-content-between">
                    <a  href="{{route('admin.department.index')}}"  class="btn cancel-btn waves-effect waves-light pl-4 pr-4 mr-3">
                        {{trans('strings.backend.general.app_back_to_list')}}
                    </a>
                    <button type="submit" class="btn add-btn waves-effect waves-light pl-4 pr-4">
                        {{trans('labels.general.buttons.update')}}
                    </button>
                </div>

            </div>

        </div>
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

                extraPlugins: 'smiley,lineutils,widget,codesnippet,prism,flash,colorbutton,colordialog',
            });

        });
        $(document).ready(function () {
            $(document).on('click', '.delete', function (e) {
                e.preventDefault();
                var parent = $(this).parent('.form-group');
                var confirmation = confirm('{{trans('strings.backend.general.are_you_sure')}}')
                if (confirmation) {
                    var media_id = $(this).data('media-id');
                    $.post('{{route('admin.media.destroy')}}', {media_id: media_id, _token: '{{csrf_token()}}'},
                        function (data, status) {
                            if (data.success) {
                                parent.remove();
                            }else{
                                alert('Something Went Wrong')
                            }
                        });
                }
            })
        })
    </script>
@endpush
