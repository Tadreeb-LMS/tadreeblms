@extends('backend.layouts.app')
@section('title', __('labels.backend.general_settings.title') . ' | ' . app_name())

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap-iconpicker/css/bootstrap-iconpicker.min.css') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('assets/css/colors/switch.css') }}">
    <style>
        .color-list li {
            float: left;
            width: 8%;
        }

        @media screen and (max-width: 768px) {
            .color-list li {
                width: 20%;
                padding-bottom: 20px;
            }

            .color-list li:first-child {
                padding-bottom: 0px;
            }
        }

        .options {
            line-height: 35px;
        }

        .color-list li a {
            font-size: 20px;
        }

        .color-list li a.active {
            border: 4px solid grey;
        }

        .color-default {
            font-size: 18px !important;
            background: #101010;
            border-radius: 100%;
        }

        .form-control-label {
            line-height: 35px;
        }

        .switch.switch-3d {
            margin-bottom: 0px;
            vertical-align: middle;

        }

        .color-default i {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .preview {
            background-color: #dcd8d8;
            background-image: url(https://www.transparenttextures.com/patterns/carbon-fibre-v2.png);
        }

        #logos img {
            height: auto;
            width: 100%;
        }
    </style>
@endpush
@section('content')
    

    <div class="card">
        <div class="card-body">
            
            <div class="row">
                <div class="col-sm-12">
                    <ul class="nav main-nav-tabs nav-tabs">
                        <li class="nav-item"><a data-toggle="tab" class="nav-link active " href="#general">
                                {{ __('Landing Page Setting') }}
                            </a>
                        </li>
                        <li class="nav-item"><a data-toggle="tab" class="nav-link " href="#footer-section">
                                {{ __('Footer Setting') }}
                            </a>
                        </li>
                        <li class="nav-item"><a data-toggle="tab" class="nav-link " href="#menu-section">
                                {{ __('Menu Setting') }}
                            </a>
                        </li>
                        <li class="nav-item"><a data-toggle="tab" class="nav-link " href="#slider-section">
                                {{ __('Slider Setting') }}
                            </a>
                        </li>
                       
                    </ul>
                    <h4 class="card-title mb-0">
                        {{-- {{ __('labels.backend.general_settings.management') }} --}}
                    </h4>
                </div><!--col-->
            </div><!--row-->

            <div class="tab-content">
                <!---General Tab--->
                <div id="general" class="tab-pane container active">
                    <div class="row mt-4 mb-4">
                        <div class="col">

                            <!-- App Name -->
                            <div class="form-group row">
                                <label for="app_name" class="col-md-4 form-control-label">
                                    {{ __('labels.backend.general_settings.disable_landing_page') }}
                                </label>
                                <div class="col-md-8">
                                    <form method="POST"
                                        action="{{ route('admin.landing-general-settings') }}"
                                        id="landing-general-settings-form"
                                        class="form-horizontal">
                                        @csrf
                                        
                                        <label class="switch switch-lg switch-3d switch-primary">
                                            <input type="checkbox"
                                                id="landing_page_toggle"
                                                class="switch-input"
                                                name="landing_page_toggle"
                                                value="1"
                                                {{ $landing_page_toggle == 1 ? 'checked' : '' }}
                                                onchange="this.form.submit();">
                                            <span class="switch-label"></span>
                                            <span class="switch-handle"></span>
                                        </label>
                                    </form>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>


                

                <!---Layout Tab--->
                <div id="footer-section" class="tab-pane container fade">
                   <form method="POST" action="{{ route('admin.general-settings') }}" 
                        id="general-settings-form" 
                        class="form-horizontal" 
                        enctype="multipart/form-data">
                    @csrf

                    <div class="pb-3">
                        <h4 class="page-title d-inline">@lang('labels.backend.general_settings.footer.title')</h4>
                    </div>

                    <div class="card">

                        <div class="card-body" id="footer">

                            <input type="hidden" id="footer_data" name="footer_data">

                            {{-- Short Description --}}
                            <div class="form-group row">
                                <label class="col-md-2 form-control-label" for="short_description">
                                    {{ __('labels.backend.general_settings.footer.short_description') }}
                                </label>

                                <div class="col-md-8">
                                    <textarea id="short_description" 
                                            name="short_description" 
                                            class="form-control"
                                            placeholder="{{ __('labels.backend.general_settings.footer.short_description') }}"></textarea>
                                </div>

                                <div class="col-md-2">
                                    <p style="line-height: 35px">
                                        <span class="mr-2">{{ __('labels.backend.general_settings.contact.show') }}</span>
                                        <label class="switch switch-sm switch-3d switch-primary">
                                            <input type="checkbox" class="switch-input status" checked value="1">
                                            <span class="switch-label"></span><span class="switch-handle"></span>
                                        </label>
                                    </p>
                                </div>
                            </div>

                            {{-- Footer Sections (1 to 3) --}}
                            @for($i=1; $i<=3; $i++)
                                <div class="form-group row">
                                    
                                    <label class="col-md-2 form-control-label">
                                        {{ __('labels.backend.general_settings.footer.section_'.$i) }}
                                    </label>

                                    <div class="col-md-8 options">
                                        <div class="row">

                                            {{-- Radio 1 --}}
                                            <div class="col-4">
                                                <label class="switch switch-sm switch-3d switch-success">
                                                    <input type="radio" name="section{{ $i }}" value="1" checked class="switch-input section{{ $i }}">
                                                    <span class="switch-label"></span><span class="switch-handle"></span>
                                                </label>
                                                <span class="ml-2 title">{{ __('labels.backend.general_settings.footer.popular_categories') }}</span>
                                            </div>

                                            {{-- Radio 2 --}}
                                            <div class="col-4">
                                                <label class="switch switch-sm switch-3d switch-success">
                                                    <input type="radio" name="section{{ $i }}" value="2" class="switch-input section{{ $i }}">
                                                    <span class="switch-label"></span><span class="switch-handle"></span>
                                                </label>
                                                <span class="ml-2 title">{{ __('labels.backend.general_settings.footer.featured_courses') }}</span>
                                            </div>

                                            {{-- Radio 3 --}}
                                            <div class="col-4">
                                                <label class="switch switch-sm switch-3d switch-success">
                                                    <input type="radio" name="section{{ $i }}" value="3" class="switch-input section{{ $i }}">
                                                    <span class="switch-label"></span><span class="switch-handle"></span>
                                                </label>
                                                <span class="ml-2 title">{{ __('labels.backend.general_settings.footer.trending_courses') }}</span>
                                            </div>

                                            {{-- Radio 4 --}}
                                            <div class="col-4 mt-2">
                                                <label class="switch switch-sm switch-3d switch-success">
                                                    <input type="radio" name="section{{ $i }}" value="4" class="switch-input section{{ $i }}">
                                                    <span class="switch-label"></span><span class="switch-handle"></span>
                                                </label>
                                                <span class="ml-2 title">{{ __('labels.backend.general_settings.footer.popular_courses') }}</span>
                                            </div>

                                            {{-- Custom Links --}}
                                            <div class="col-4 mt-2">
                                                <label class="switch switch-sm switch-3d switch-success">
                                                    <input type="radio" name="section{{ $i }}" value="5" class="switch-input custom_links section{{ $i }}">
                                                    <span class="switch-label"></span><span class="switch-handle"></span>
                                                </label>
                                                <span class="ml-2 title">{{ __('labels.backend.general_settings.footer.custom_links') }}</span>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <p style="line-height: 35px">
                                            <span class="mr-2">{{ __('labels.backend.general_settings.contact.show') }}</span>
                                            <label class="switch switch-sm switch-3d switch-primary">
                                                <input type="checkbox" class="switch-input status" checked value="1">
                                                <span class="switch-label"></span><span class="switch-handle"></span>
                                            </label>
                                        </p>
                                    </div>

                                    <div class="col-10 offset-2 button-container"></div>

                                </div>
                            @endfor


                            {{-- Social Links --}}
                            <div class="form-group row">
                                <label class="col-md-2 form-control-label">
                                    {{ __('labels.backend.general_settings.footer.social_links') }}
                                </label>

                                <div class="col-md-4 my-1">
                                    <input type="text" id="social_link_url" class="form-control"
                                        placeholder="{{ __('labels.backend.general_settings.footer.link_url') }}">
                                    <span class="error text-danger"></span>
                                </div>

                                <div class="col-md-2 my-1">
                                    <button type="button" id="icon" class="btn btn-default border btn-block"></button>
                                </div>

                                <div class="col-md-2 my-1">
                                    <button type="button" class="btn btn-light add-social-link border btn-block">
                                        {{ trans('strings.backend.general.app_add') }} <i class="fa fa-plus"></i>
                                    </button>
                                </div>

                                <div class="col-md-2 my-1">
                                    <label class="switch switch-sm switch-3d switch-primary">
                                        <input type="checkbox" class="switch-input status" checked value="1">
                                        <span class="switch-label"></span><span class="switch-handle"></span>
                                    </label>
                                </div>

                                <div class="col-md-10 offset-2">
                                    <p class="font-italic">{!! __('labels.backend.general_settings.footer.social_links_note') !!}</p>
                                </div>

                                <div class="col-md-8 offset-2 social-links-container"></div>
                            </div>


                            {{-- Newsletter --}}
                            <div class="form-group row">
                                <label class="col-md-2 form-control-label">
                                    {{ __('labels.backend.general_settings.footer.newsletter_form') }}
                                </label>

                                <div class="col-md-2">
                                    <label class="switch switch-sm switch-3d switch-primary">
                                        <input type="checkbox" class="switch-input newsletter-form status" checked value="1">
                                        <span class="switch-label"></span><span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>


                            {{-- Bottom Footer --}}
                            <div class="form-group row">
                                <label class="col-md-2 form-control-label">
                                    {{ __('labels.backend.general_settings.footer.bottom_footer') }}
                                </label>

                                <div class="col-md-10">
                                    <label class="switch switch-sm switch-3d switch-primary">
                                        <input type="checkbox" class="switch-input bottom-footer status" checked value="1">
                                        <span class="switch-label"></span><span class="switch-handle"></span>
                                    </label>
                                    <span class="ml-3 font-italic">{{ __('labels.backend.general_settings.footer.bottom_footer_note') }}</span>
                                </div>
                            </div>


                            {{-- Copyright --}}
                            <div class="form-group row">
                                <label class="col-md-2 form-control-label" for="copyright_text">
                                    {{ __('labels.backend.general_settings.footer.copyright_text') }}
                                </label>

                                <div class="col-md-8">
                                    <input type="text" id="copyright_text" 
                                        class="form-control"
                                        placeholder="{{ __('labels.backend.general_settings.footer.copyright_text') }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="switch switch-sm switch-3d switch-primary">
                                        <input type="checkbox" class="switch-input status" checked value="1">
                                        <span class="switch-label"></span><span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>


                            {{-- Footer Links --}}
                            <div class="form-group row">
                                <label class="col-md-2 form-control-label">
                                    {{ __('labels.backend.general_settings.footer.footer_links') }}
                                </label>

                                <div class="col-md-4 my-1">
                                    <input type="text" id="footer_link_url" class="form-control"
                                        placeholder="{{ __('labels.backend.general_settings.footer.link_url') }}">
                                    <span class="error text-danger"></span>
                                </div>

                                <div class="col-md-2 my-1">
                                    <input type="text" id="footer_link_label" class="form-control"
                                        placeholder="{{ __('labels.backend.general_settings.footer.link_label') }}">
                                </div>

                                <div class="col-md-2 my-1">
                                    <button type="button" class="btn btn-light btn-block add-footer-link border">
                                        {{ trans('strings.backend.general.app_add') }} <i class="fa fa-plus"></i>
                                    </button>
                                </div>

                                <div class="col-md-2 my-1">
                                    <label class="switch switch-sm switch-3d switch-primary">
                                        <input type="checkbox" class="switch-input status" checked value="1">
                                        <span class="switch-label"></span><span class="switch-handle"></span>
                                    </label>
                                </div>

                                <div class="col-md-8 offset-2 footer-links-container"></div>

                            </div>

                        </div>

                        {{-- Footer Submit --}}
                        <div class="row p-3">
                            <div class="col">
                                <a href="{{ route('admin.general-settings') }}" class="btn btn-secondary">
                                    {{ __('buttons.general.cancel') }}
                                </a>
                            </div>

                            <div class="col text-right">
                                <button type="submit" id="submit" class="btn btn-primary">
                                    {{ __('buttons.general.crud.update') }}
                                </button>
                            </div>
                        </div>

                    </div>

                    </form>
                </div>


                <!---MENU Tab--->
                <div id="menu-section" class="tab-pane container fade">
                    <div class="card">
                        <div class="card-body">
                            @if(isset($menu))
                                {!! Menu::render()->with(['menu' => $menu,'menu_list' => $menu_list,'pages' =>$pages]) !!}
                            @else
                                {!! Menu::render()->with(['menu_list' => $menu_list]) !!}
                            @endif
                        </div>
                    </div>
                </div>

                <!---Hero Slider Tab--->
                <div id="slider-section" class="tab-pane container fade">
                    <div class="pb-3 d-flex justify-content-between align-items-center">
                        <h4>@lang('labels.backend.hero_slider.title')</h4>
                        <div>
                            <a href="{{ route('admin.sliders.create') }}" class="add-btn">
                                @lang('strings.backend.general.app_add_new')
                            </a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="myTable" class="table custom-teacher-table table-striped">
                                    <thead>
                                        <tr>
                                            <th>@lang('labels.general.sr_no')</th>
                                            <th>ID</th>
                                            <th>@lang('labels.backend.hero_slider.fields.name')</th>
                                            <th>@lang('labels.backend.hero_slider.fields.bg_image')</th>
                                            <th>@lang('labels.backend.hero_slider.fields.sequence')</th>
                                            <th>@lang('labels.backend.hero_slider.fields.status')</th>
                                            <th class="text-center">@lang('strings.backend.general.actions')</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($slides_list as $key => $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>

                                            <td>{{ $item->id }}</td>

                                            <td>{{ $item->name }}</td>

                                            <td>
                                                <img src="{{ asset('storage/uploads/'.$item->bg_image) }}" height="50">
                                            </td>

                                            <td>{{ $item->sequence }}</td>

                                            <td>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox"
                                                        class="custom-control-input status-toggle"
                                                        id="switch{{ $item->id }}"
                                                        data-id="{{ $item->id }}"
                                                        value="1"
                                                        {{ $item->status ? 'checked' : '' }}>
                                                    <label class="custom-control-label"
                                                        for="switch{{ $item->id }}">
                                                    </label>
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm p-1" type="button"
                                                        id="actionDropdown{{ $item->id }}"
                                                        data-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v text-muted"></i>
                                                    </button>

                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a href="{{ route('admin.sliders.edit', $item->id) }}" class="dropdown-item">
                                                            Edit
                                                        </a>

                                                        <a class="dropdown-item" style="cursor:pointer;"
                                                            onclick="$(this).find('form').submit();">
                                                            Delete
                                                            <form action="{{ route('admin.sliders.destroy', $item->id) }}"
                                                                method="POST" style="display:none">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>

                                        </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Sequence Section --}}
                    <div class="pb-3">
                        <h4>@lang('labels.backend.hero_slider.manage_sequence')</h4>
                    </div>

                    <div class="card">
                        <div class="card-body">

                            @if(count($slides_list))
                            <div class="row justify-content-center">
                                <div class="col-md-6">

                                    <h4>@lang('labels.backend.hero_slider.sequence_note')</h4>

                                    <ul class="sorter list-unstyled">
                                        @foreach($slides_list as $item)
                                        <li class="mb-2">
                                            <span data-id="{{ $item->id }}"
                                                data-sequence="{{ $item->sequence }}"
                                                class="d-block p-2 bg-light border rounded">
                                                <span class="ml-2">{{ $item->name }}</span>
                                            </span>
                                        </li>
                                        @endforeach
                                    </ul>

                                    <div class="d-flex justify-content-between mt-3">

                                        <a href="{{ route('admin.courses.index') }}" class="cancel-btn">
                                            @lang('strings.backend.general.app_back_to_list')
                                        </a>

                                        <a href="#" id="save_timeline" class="add-btn">
                                            @lang('labels.backend.hero_slider.save_sequence')
                                        </a>

                                    </div>
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    
@endsection


@push('after-scripts')
    <script src="{{ asset('plugins/bootstrap-iconpicker/js/bootstrap-iconpicker.bundle.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let submitting = false;

            const toggle = document.getElementById('landing_page_toggle');
            const form   = document.getElementById('landing-general-settings-form');

            if (toggle && form) {
                toggle.addEventListener('change', function () {
                    if (submitting) return;
                    submitting = true;
                    form.submit();
                });
            }
        });
    </script>
    <script>
        $(document).ready(function() {



            @if (request()->has('tab'))
                var tab = "{{ request('tab') }}";
                $('.nav-tabs a[href="#' + tab + '"]').tab('show');
            @endif

            //========= Initialisation for Iconpicker ===========//
            $('#icon').iconpicker({
                cols: 10,
                icon: 'fab fa-facebook-f',
                iconset: 'fontawesome5',
                labelHeader: '{0} of {1} pages',
                labelFooter: '{0} - {1} of {2} icons',
                placement: 'bottom', // Only in button tag
                rows: 5,
                search: true,
                searchText: 'Search',
                selectedClass: 'btn-success',
                unselectedClass: ''
            });


            //========== Preset theme layout ==============//
            @if (config('theme_layout') != '')
                $('#theme_layout').find('option').removeAttr('selected')
                $('#theme_layout').find('option[value="{{ config('theme_layout') }}"]').attr('selected',
                    'selected');
            @endif


            //============ Preset font color ===============//
            @if (config('font_color') != '')
                $('.color-list').find('li a').removeClass('active');
                $('.color-list').find('li a[data-color="{{ config('font_color') }}"]').addClass('active');
                $('#font_color').val("{{ config('font_color') }}");
            @endif


            //========= Preset Layout type =================//
            @if (config('layout_type') != '')
                $('#layout_type').find('option').removeAttr('selected')
                $('#layout_type').find('option[value="{{ config('layout_type') }}"]').attr('selected',
                    'selected');
            @endif


            //=========== Preset Counter data =============//
            @if (config('counter') != '')
                @if ((int) config('counter') == 1)
                    $('.counter-container').removeClass('d-none')
                    $('#total_students').val("{{ config('total_students') }}");
                    $('#total_teachers').val("{{ config('total_teachers') }}");
                    $('#total_courses').val("{{ config('total_courses') }}");
                @else
                    $('#counter-container').empty();
                @endif

                @if (config('counter') != '')
                    $('.counter-container').removeClass('d-none');
                @endif

                $('#counter').find('option').removeAttr('selected')
                $('#counter').find('option[value="{{ config('counter') }}"]').attr('selected', 'selected');
            @endif


            //======== Preset PaymentMode for Paypal =======>
            @if (config('paypal.settings.mode') != '')
                $('#paypal_settings_mode').find('option').removeAttr('selected')
                $('#paypal_settings_mode').find('option[value="{{ config('paypal.settings.mode') }}"]').attr(
                    'selected', 'selected');
            @endif

            //======== Preset PaymentMode for Instamojo =======>
            @if (config('services.instamojo.mode') != '')
                $('#instamojo_settings_mode').find('option').removeAttr('selected')
                $('#instamojo_settings_mode').find('option[value="{{ config('services.instamojo.mode') }}"]')
                    .attr('selected', 'selected');
            @endif

            //======== Preset PaymentMode for Cashfree =======>
            @if (config('services.cashfree.mode') != '')
                $('#cashfree_settings_mode').find('option').removeAttr('selected')
                $('#cashfree_settings_mode').find('option[value="{{ config('services.cashfree.mode') }}"]').attr(
                    'selected', 'selected');
            @endif

            //======== Preset PaymentMode for PayUMoney =======>
            @if (config('services.payu.mode') != '')
                $('#cashfree_settings_mode').find('option').removeAttr('selected')
                $('#cashfree_settings_mode').find('option[value="{{ config('services.payu.mode') }}"]').attr(
                    'selected', 'selected');
            @endif

            //======== Preset PaymentMode for Flutter =======>
            @if (config('rave.env') != '')
                $('#rave_env').find('option').removeAttr('selected')
                $('#rave_env').find('option[value="{{ config('rave.env') }}"]').attr('selected', 'selected');
            @endif


            //============= Font Color selection =================//
            $(document).on('click', '.color-list li', function() {
                $(this).siblings('li').find('a').removeClass('active')
                $(this).find('a').addClass('active');
                $('#font_color').val($(this).find('a').data('color'));
            });


            

            






         

            @if (request()->has('tab'))
                var tab = "{{ request('tab') }}";
                $('.nav-tabs a[href="#' + tab + '"]').tab('show');
            @endif

        });

        $(document).on('click', '.switch-input', function(e) {
            //              e.preventDefault();
            var content = $(this).parents('.checkbox').siblings('.switch-content');
            if (content.hasClass('d-none')) {
                $(this).attr('checked', 'checked');
                content.find('input').attr('required', true);
                content.removeClass('d-none');
            } else {
                content.addClass('d-none');
                content.find('input').attr('required', false);
            }
        })


        
    </script>
@endpush
