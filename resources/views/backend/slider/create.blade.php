@extends('backend.layouts.app')
@section('title', __('labels.backend.hero_slider.title').' | '.app_name())

@push('after-styles')
    <style>
        .form-control-label {
            line-height: 35px;
        }
        .remove{
            float: right;
            color: red;
            font-size: 20px;
            cursor: pointer;
        }
        .error{
            color: red;
        }

    </style>

    <link rel="stylesheet" type="text/css"
          href="{{asset('plugins/jqueryui-datetimepicker/jquery.datetimepicker.css')}}">
@endpush
@section('content')
    {{ html()->form('POST', route('admin.sliders.store'))->id('slider-create')->class('form-horizontal')->acceptsFiles()->open() }}
    <div class="alert alert-danger d-none" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
        <div class="error-list">
        </div>
    </div>
    <div class="pb-3 d-flex justify-content-between align-items-center">
        <h4 class="">@lang('labels.backend.hero_slider.create')</h4>
        <div class="">
            <a href="{{ route('admin.sliders.index') }}"
               class="add-btn">@lang('labels.backend.hero_slider.view')</a>

        </div>
    </div>
    <div class="card">
        <div class="card-body">

            <div class="row form-group">
                {{ html()->label(__('labels.backend.hero_slider.fields.name'))->class('col-md-2 form-control-label')->for('first_name') }}

                <div class="col-md-10">
                    {{ html()->text('name')
                        ->class('form-control')
                        ->placeholder(__('labels.backend.hero_slider.fields.name'))
                        ->attribute('maxlength', 191)
                    ->autofocus()
                    }}

                </div><!--col-->
            </div>

            <div class="row form-group">
                {{ html()->label(__('labels.backend.hero_slider.fields.bg_image'))->class('col-md-2 form-control-label')->for('image') }}

                <div class="col-md-10">
                    {{-- {!! Form::file('image', ['class' => 'form-control d-inline-block', 'placeholder' => '', 'accept' => 'image/jpeg,image/gif,image/png']) !!}
                    {!! Form::hidden('slide_image_max_size', 8) !!}
                    {!! Form::hidden('slide_image_max_width', 4000) !!}
                    {!! Form::hidden('slide_image_max_height', 4000) !!} --}}
                                    <div class="custom-file-upload-wrapper">
    <input type="file" name="image" id="customFileInput" class="custom-file-input">
    <label for="customFileInput" class="custom-file-label">
        <i class="fa fa-upload mr-1"></i> Choose a file
    </label>
</div>
</div>
<div class="col-md-2"></div>
<div class="col-md-10">

    <p class="help-text mb-0 font-italic">{!!  __('labels.backend.hero_slider.note')!!}</p>
</div>
            </div>
            {{-- <div class="row form-group">
                {{ html()->label(__('labels.backend.hero_slider.fields.overlay.title'))->class('col-md-2 form-control-label')->for('overlay') }}

                <div class="col-md-10">
                    {{ html()->label(html()->checkbox('overlay')
                                              ->class('switch-input')->value(1)
                                        . '<span class="switch-label"></span><span class="switch-handle"></span>')
                                    ->class('switch switch-sm switch-3d switch-primary')
                                }}
                    <p class="help-text mb-0 font-italic">{!!  __('labels.backend.hero_slider.fields.overlay.note')!!}</p>
                </div>
            </div>
            <div class="row form-group">
                {{ html()->label(__('labels.backend.hero_slider.fields.hero_text'))->class('col-md-2 form-control-label')->for('hero_text') }}

                <div class="col-md-10">
                    {{ html()->text('hero_text')
                        ->class('form-control')
                        ->placeholder(__('labels.backend.hero_slider.fields.hero_text'))
                        }}
                </div>
            </div>
            <div class="row form-group">
                {{ html()->label(__('labels.backend.hero_slider.fields.sub_text'))->class('col-md-2 form-control-label')->for('sub_text') }}
                <div class="col-md-10">
                    {{ html()->text('sub_text')
                        ->class('form-control')
                        ->placeholder(__('labels.backend.hero_slider.fields.sub_text'))
                         }}
                </div><!--col-->
            </div>
            <div class="row form-group">
                {{ html()->label(__('labels.backend.hero_slider.fields.widget.title'))->class('col-md-2 form-control-label')->for('widget') }}
                <div class="col-md-10">
                    {!! Form::select('widget', [""=>trans('labels.backend.hero_slider.fields.widget.select_widget'),1=>trans('labels.backend.hero_slider.fields.widget.search_bar'),2=>trans('labels.backend.hero_slider.fields.widget.countdown_timer')],  (request('widget')) ? request('widget') : old('widget'), ['class' => 'form-control ', 'id'=>'widget']) !!}

                    <div class="widget-container mt-2 d-none">
                        {{ html()->text('timer')
                           ->class('form-control')
                           ->placeholder(trans('labels.backend.hero_slider.fields.widget.input_date_time'))
                         ->id('timer')
                       }}
                    </div>
                </div><!--col-->
            </div>
            <div class="row form-group">
                {{ html()->label(__('labels.backend.hero_slider.fields.buttons.title'))->class('col-md-2 col-2 form-control-label')->for('buttons') }}
                <div class="col-2">
                    <button type="button" id="add-button" class="btn-block btn  btn-primary">{{__('labels.backend.hero_slider.fields.buttons.add')}}</button>
                </div>


                <div class="col-md-8 col-8">
                    
                    <p class="help-text mb-0 font-italic">{!!  __('labels.backend.hero_slider.fields.buttons.note')!!}</p>
                </div><!--col-->
                <div class="col-12 col-md-10 ml-auto button-container mt-2">

                </div>

            </div> --}}

            <div class=" row ">
                <div class="col-12 d-flex justify-content-between ">
                    <div>

                        {{ form_cancel(route('admin.sliders.index'), __('buttons.general.cancel')) }}
                    </div>
                    <div>

                        <button class="add-btn pull-right" type="button" id="btn-submit">{{__('buttons.general.crud.create')}}</button>
                    </div>

                </div>
            </div><!--col-->
        </div>
    </div>
    {{ html()->form()->close() }}
@endsection

@push('after-scripts')
    <script src="{{asset('plugins/jqueryui-datetimepicker/jquery.datetimepicker.full.min.js')}}"></script>

    <script src="{{asset('js/slider-form.js')}}"></script>
    <script>

        $(document).ready(function () {
            $(".js-example-placeholder-single").select2({
                placeholder: "{{trans('labels.backend.hero_slider.fields.widget.select_widget')}}",
            });

            $('#timer').datetimepicker({
               mask:true
            });

            $(document).on('change', '#widget', function () {
                if ($(this).val() == 2) {
                    $('.widget-container').removeClass('d-none');
                } else {
                    $('.widget-container').addClass('d-none');
                }
            });

            $(document).on('click',',add-button',function (e) {
                e.preventDefault()

                if($('.button-wrapper').length <= 3){
                    var name = 'Button';
                    var html = "<div class='button-wrapper'> <h6 class='mt-3'> "+name+" <span class='remove'><i class='fa fa-window-close'></i></span></h6>" +
                        "<div class='row'>" +
                        "<div class='col-lg-6'>" +
                        "<input type='text' required name='button_label' class='form-control' placeholder='Button label'>" +
                        "</div>" +
                        "<div class='col-lg-6'>" +
                        "<input type='text' required name='button_link' class='form-control' placeholder='Button Link'>" +
                        "</div>" +
                        "</div></div>";

                    $('.button-container').append(html);
                }else{
                    alert('Maximum limit of button exceeded!')
                }

            });

            $(document).on('click','.remove',function () {
                if(confirm('Are you sure want to remove button?')){
                    $(this).parents('.button-wrapper').remove();
                    $('#buttons').val($('.button-wrapper').length)
                }
            });


        })
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
