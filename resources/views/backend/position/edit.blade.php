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
    
<form action="{{ route('admin.position.update',$page->id) }}" method="post" enctype="multipart/form-data">
    @csrf()  
    <div class="card">
        <div class="card-header">
            <h3 class="page-title float-left mb-0">Edit Position</h3>
            <div class="float-right">
                <a href="{{ route('admin.position.index') }}"
                   class="btn btn-success">View Position</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 form-group">
                    
                    <label for="title" class="control-label">Title</label>
                    <input value="{{ $page->title }}" class="form-control" placeholder="Title" name="title" type="text" id="title">
                    
                </div>
            </div>


           


            <div class="row">
                <div class="col-12 form-group">
                    <label for="content" class="control-label">Description</label>
                    <textarea class="form-control" placeholder="Enter description" name="content" rows="4">{{ $page->content }}</textarea>
                </div>
            </div>

           

            <div class="row">


                <div class="col-md-12 text-center form-group">
                    <button type="submit" class="btn btn-info waves-effect waves-light ">
                        {{trans('labels.general.buttons.update')}}
                    </button>
                    <a href="{{route('admin.position.index')}}" class="btn btn-danger waves-effect waves-light ">
                        {{trans('strings.backend.general.app_back_to_list')}}
                    </a>
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
