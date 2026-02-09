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

        .error{
            margin-top: 5px;
            color: green;
            font-weight: bold;
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
                                {{ __('LDAP Setting') }}
                            </a>
                        </li>
                        {{-- <li class="nav-item"><a data-toggle="tab" class="nav-link " href="#footer-section">
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
                        </li> --}}
                       
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

                            <form method="POST"
                                action="{{ route('admin.ldap-settings') }}"
                                id="landing-general-settings-form"
                                class="form-horizontal">
                                @csrf

                                <div class="form-group row">
                                    <label class="col-md-4">Enable LDAP</label>
                                    <div class="col-md-8">
                                        <label class="switch switch-lg switch-3d switch-primary">
                                            <input type="checkbox"
                                                class="switch-input"
                                                name="ldap_toggle"
                                                value="1"
                                                {{ $ldap_toggle == 1 ? 'checked' : '' }}>
                                            <span class="switch-label"></span>
                                            <span class="switch-handle"></span>
                                        </label>
                                    </div>
                                </div>

                                <hr>

                                <div class="form-group row">
                                    <label class="col-md-4">LDAP Host</label>
                                    <div class="col-md-8">
                                        <input type="text" name="ldap_host" class="form-control"
                                            value="{{ $ldap_host ?? '127.0.0.1' }}">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-4">LDAP Port</label>
                                    <div class="col-md-8">
                                        <input type="text" name="ldap_port" class="form-control"
                                            value="{{ $ldap_port ?? '389' }}">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-4">Base DN</label>
                                    <div class="col-md-8">
                                        <input type="text" name="ldap_base_dn" class="form-control"
                                            value="{{ $ldap_base_dn ?? 'dc=mycompany,dc=local' }}">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-4">Admin Username (Bind DN)</label>
                                    <div class="col-md-8">
                                        <input type="text" name="ldap_username" class="form-control"
                                            value="{{ $ldap_username ?? 'cn=admin,dc=mycompany,dc=local' }}">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-4">Admin Password</label>
                                    <div class="col-md-8">
                                        <input type="password" name="ldap_password" class="form-control"
                                            value="{{ $ldap_password ?? '' }}">
                                    </div>
                                </div>

                                <button type="button" id="saveLdapBtn" class="btn btn-primary mt-3">
                                    Save Configuration
                                </button>

                                <div id="ldapStatusMsg" class=" error"></div>

                            </form>
                            <br>
                            <hr>
                            <div class="mt-20">
                            <h3>Test LDAP Connection</h3>
                            <form>
                                <button type="button" id="test_ldap_connection" class="btn btn-primary mt-3">
                                    Test LDAP Connection
                                </button>
                                <div id="ldapStatus" class="error"></div>
                            </form>
                            </div>

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
    $('#saveLdapBtn').click(function() {

        let formData = {
            _token: "{{ csrf_token() }}",
            ldap_toggle: $('input[name="ldap_toggle"]').is(':checked') ? 1 : 0,
            ldap_host: $('input[name="ldap_host"]').val(),
            ldap_port: $('input[name="ldap_port"]').val(),
            ldap_base_dn: $('input[name="ldap_base_dn"]').val(),
            ldap_username: $('input[name="ldap_username"]').val(),
            ldap_password: $('input[name="ldap_password"]').val(),
        };

        

        // STEP 1: Save to .env
        $.post("{{ route('admin.ldap.save.env') }}", formData, function(res) {

            
            if (res.status === 'success') {
                $('#ldapStatusMsg').html('<span class="text-success">' + res.message + '</span>');
            } else {
                $('#ldapStatusMsg').html('<span class="text-danger">' + res.message + '</span>');
            }

        });
    });

    $('#test_ldap_connection').click(function() {

        let formData = {
            _token: "{{ csrf_token() }}",
            ldap_toggle: $('input[name="ldap_toggle"]').is(':checked') ? 1 : 0,
            ldap_host: $('input[name="ldap_host"]').val(),
            ldap_port: $('input[name="ldap_port"]').val(),
            ldap_base_dn: $('input[name="ldap_base_dn"]').val(),
            ldap_username: $('input[name="ldap_username"]').val(),
            ldap_password: $('input[name="ldap_password"]').val(),
        };

        // STEP 1: Save to .env
        $.post("{{ route('admin.ldap.test') }}", formData, function(res) {

            if (res.status === 'connected') {
                $('#ldapStatus').html('<span class="text-success">' + res.message + '</span>');
            } else {
                $('#ldapStatus').html('<span class="text-danger">' + res.message + '</span>');
            }

        });
    });

    




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
