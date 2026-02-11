@extends('backend.layouts.app')
@section('title', __('labels.backend.smtp_settings.title').' | '.app_name())

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- Alert Container --}}
            <div id="smtpAlertContainer"></div>

            {{-- SMTP Configuration Section --}}
            <form id="smtpSettingsForm" method="POST">
                @csrf

                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-5">
                                <h4 class="card-title mb-0">
                                    <i class="fas fa-envelope-open-text mr-2"></i>
                                    {{ __('labels.backend.smtp_settings.title') }}
                                </h4>
                            </div>
                        </div>

                        <hr/>

                        <div class="row mt-4 mb-4">
                            <div class="col">
                                {{-- Mail Mailer --}}
                                <div class="form-group row">
                                    <label for="mail_mailer" class="col-md-2 form-control-label">{{ __('labels.backend.smtp_settings.mail_mailer') }}*</label>
                                    <div class="col-md-10">
                                        <select name="mail_mailer" id="mail_mailer" class="form-control" required>
                                            <option value="smtp" {{ $smtpSettings['mail_mailer'] == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                            <option value="sendmail" {{ $smtpSettings['mail_mailer'] == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                            <option value="mailgun" {{ $smtpSettings['mail_mailer'] == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                            <option value="ses" {{ $smtpSettings['mail_mailer'] == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                            <option value="postmark" {{ $smtpSettings['mail_mailer'] == 'postmark' ? 'selected' : '' }}>Postmark</option>
                                            <option value="log" {{ $smtpSettings['mail_mailer'] == 'log' ? 'selected' : '' }}>Log (for testing)</option>
                                        </select>
                                        <span class="help-text font-italic">{{ __('labels.backend.smtp_settings.mail_mailer_note') }}</span>
                                    </div>
                                </div>

                                {{-- Mail Host --}}
                                <div class="form-group row">
                                    <label for="mail_host" class="col-md-2 form-control-label">{{ __('labels.backend.smtp_settings.mail_host') }}*</label>
                                    <div class="col-md-10">
                                        <input type="text" name="mail_host" id="mail_host" class="form-control" value="{{ $smtpSettings['mail_host'] }}" placeholder="smtp.gmail.com" required>
                                        <span class="help-text font-italic">{{ __('labels.backend.smtp_settings.mail_host_note') }}</span>
                                    </div>
                                </div>

                                {{-- Mail Port --}}
                                <div class="form-group row">
                                    <label for="mail_port" class="col-md-2 form-control-label">{{ __('labels.backend.smtp_settings.mail_port') }}*</label>
                                    <div class="col-md-10">
                                        <input type="number" name="mail_port" id="mail_port" class="form-control" value="{{ $smtpSettings['mail_port'] }}" placeholder="587" required min="1" max="65535">
                                        <span class="help-text font-italic">{{ __('labels.backend.smtp_settings.mail_port_note') }}</span>
                                    </div>
                                </div>

                                {{-- Mail Username --}}
                                <div class="form-group row">
                                    <label for="mail_username" class="col-md-2 form-control-label">{{ __('labels.backend.smtp_settings.mail_username') }}</label>
                                    <div class="col-md-10">
                                        <input type="text" name="mail_username" id="mail_username" class="form-control" value="{{ $smtpSettings['mail_username'] }}" placeholder="your-email@gmail.com">
                                        <span class="help-text font-italic">{{ __('labels.backend.smtp_settings.mail_username_note') }}</span>
                                    </div>
                                </div>

                                {{-- Mail Password --}}
                                <div class="form-group row">
                                    <label for="mail_password" class="col-md-2 form-control-label">{{ __('labels.backend.smtp_settings.mail_password') }}</label>
                                    <div class="col-md-10">
                                        <div class="input-group">
                                            <input type="password" name="mail_password" id="mail_password" class="form-control" placeholder="{{ $smtpSettings['mail_password'] ? '************' : __('labels.backend.smtp_settings.mail_password_placeholder') }}">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <span class="help-text font-italic">{{ __('labels.backend.smtp_settings.mail_password_note') }}</span>
                                        @if($smtpSettings['mail_password'])
                                            <span class="text-muted small d-block mt-1">
                                                <i class="fas fa-info-circle"></i> {{ __('labels.backend.smtp_settings.password_exists') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Mail Encryption --}}
                                <div class="form-group row">
                                    <label for="mail_encryption" class="col-md-2 form-control-label">{{ __('labels.backend.smtp_settings.mail_encryption') }}</label>
                                    <div class="col-md-10">
                                        <select name="mail_encryption" id="mail_encryption" class="form-control">
                                            <option value="tls" {{ $smtpSettings['mail_encryption'] == 'tls' ? 'selected' : '' }}>TLS</option>
                                            <option value="ssl" {{ $smtpSettings['mail_encryption'] == 'ssl' ? 'selected' : '' }}>SSL</option>
                                            <option value="null" {{ ($smtpSettings['mail_encryption'] == 'null' || $smtpSettings['mail_encryption'] == '') ? 'selected' : '' }}>None</option>
                                        </select>
                                        <span class="help-text font-italic">{{ __('labels.backend.smtp_settings.mail_encryption_note') }}</span>
                                    </div>
                                </div>

                                {{-- Mail From Address --}}
                                <div class="form-group row">
                                    <label for="mail_from_address" class="col-md-2 form-control-label">{{ __('labels.backend.smtp_settings.mail_from_address') }}*</label>
                                    <div class="col-md-10">
                                        <input type="email" name="mail_from_address" id="mail_from_address" class="form-control" value="{{ $smtpSettings['mail_from_address'] }}" placeholder="noreply@example.com" required>
                                        <span class="help-text font-italic">{{ __('labels.backend.smtp_settings.mail_from_address_note') }}</span>
                                    </div>
                                </div>

                                {{-- Mail From Name --}}
                                <div class="form-group row">
                                    <label for="mail_from_name" class="col-md-2 form-control-label">{{ __('labels.backend.smtp_settings.mail_from_name') }}*</label>
                                    <div class="col-md-10">
                                        <input type="text" name="mail_from_name" id="mail_from_name" class="form-control" value="{{ $smtpSettings['mail_from_name'] }}" placeholder="Your Application Name" required>
                                        <span class="help-text font-italic">{{ __('labels.backend.smtp_settings.mail_from_name_note') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer clearfix">
                        <div class="row">
                            <div class="col">
                                <a href="{{ route('admin.smtp-settings') }}" class="btn btn-secondary">{{ __('buttons.general.cancel') }}</a>
                            </div>
                            <div class="col text-right">
                                <button type="submit" class="btn btn-primary" id="btnSaveSettings">
                                    <i class="fas fa-save mr-1"></i> {{ __('buttons.general.crud.update') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Test Email Section --}}
            <form id="testEmailForm" method="POST">
                @csrf

                <div class="card mt-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-5">
                                <h4 class="card-title mb-0">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    {{ __('labels.backend.smtp_settings.test_email_title') }}
                                </h4>
                            </div>
                        </div>

                        <hr/>

                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle mr-2"></i>
                            {{ __('labels.backend.smtp_settings.test_email_info') }}
                        </div>

                        <div class="row mt-4 mb-4">
                            <div class="col">
                                <div class="form-group row">
                                    <label for="test_email" class="col-md-2 form-control-label">{{ __('labels.backend.smtp_settings.test_email_address') }}*</label>
                                    <div class="col-md-10">
                                        <input type="email" name="test_email" id="test_email" class="form-control" value="{{ auth()->user()->email }}" placeholder="test@example.com" required>
                                        <span class="help-text font-italic">{{ __('labels.backend.smtp_settings.test_email_note') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer clearfix">
                        <div class="row">
                            <div class="col text-right">
                                <button type="submit" class="btn btn-info" id="btnTestEmail">
                                    <i class="fas fa-paper-plane mr-1"></i>
                                    {{ __('labels.backend.smtp_settings.send_test_email') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('after-scripts')
<script>
$(document).ready(function() {
    // Toggle password visibility
    $('#togglePassword').on('click', function() {
        var passwordField = $('#mail_password');
        var toggleIcon = $('#toggleIcon');
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            toggleIcon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            toggleIcon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Show alert function
    function showAlert(type, message) {
        var alertClass = (type === 'success') ? 'alert-success' : 'alert-danger';
        var iconClass = (type === 'success') ? 'fa-check-circle' : 'fa-exclamation-triangle';
        var html = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
            '<i class="fas ' + iconClass + ' mr-2"></i>' + message +
            '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>' +
            '</div>';
        $('#smtpAlertContainer').html(html);
        window.scrollTo(0, 0);
    }

    // Save SMTP Settings
    $('#smtpSettingsForm').on('submit', function(e) {
        e.preventDefault();

        var btn = $('#btnSaveSettings');
        var btnHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url: '{{ route("admin.smtp-settings.save") }}',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                showAlert('success', response.message || 'Settings saved successfully!');
                btn.prop('disabled', false).html(btnHtml);
            },
            error: function(xhr) {
                var msg = 'Failed to save settings.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        var errors = [];
                        $.each(xhr.responseJSON.errors, function(key, val) {
                            errors.push(val[0]);
                        });
                        msg = errors.join('<br>');
                    }
                }
                showAlert('error', msg);
                btn.prop('disabled', false).html(btnHtml);
            }
        });
    });

    // Send Test Email
    $('#testEmailForm').on('submit', function(e) {
        e.preventDefault();

        var btn = $('#btnTestEmail');
        var btnHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Sending...');

        $.ajax({
            url: '{{ route("admin.smtp-settings.test") }}',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                showAlert('success', response.message || 'Test email sent successfully!');
                btn.prop('disabled', false).html(btnHtml);
            },
            error: function(xhr) {
                var msg = 'Failed to send test email.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        var errors = [];
                        $.each(xhr.responseJSON.errors, function(key, val) {
                            errors.push(val[0]);
                        });
                        msg = errors.join('<br>');
                    }
                }
                showAlert('error', msg);
                btn.prop('disabled', false).html(btnHtml);
            }
        });
    });
});
</script>
@endpush
