<style>
.modal-dialog {
    margin: 1.75em auto;
    min-height: calc(100vh - 60px);
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.modal_close {
    position: absolute;
    top: -13px;
    right: -4px;
    color: #fff;
    background: linear-gradient(to right, #7ba91f 0%, #a1bf62 51%, #9dc15d 100%) !important;
    font-size: 28px;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    border: 4px solid #fff;
}
</style>

@if(!auth()->check())

<!-- LOGIN MODAL -->
<div class="modal fade" id="myModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<div class="backgroud-style text-center">
    <h2>@lang('labels.frontend.modal.my_account')</h2>
    <p>Login to continue</p>
    <button type="button" class="close modal_close" data-dismiss="modal">×</button>
</div>

<div class="modal-body">
<form id="loginForm" action="{{ route('frontend.auth.login.post') }}" method="POST">
@csrf

<input type="hidden" name="redirect_url" id="redirect_url">

<div class="contact-info mb-2">
    <input type="email" name="email" class="form-control" placeholder="Email">
    <span id="login-email-error" class="text-danger"></span>
</div>

<div class="contact-info mb-2">
    <input type="password" name="password" class="form-control" placeholder="Password">
    <span id="login-password-error" class="text-danger"></span>
</div>

<!-- CAPTCHA -->
<div class="contact-info mb-2">
    <label>Captcha: <strong id="login-captcha-question"></strong></label>
    <input type="text" name="captcha" class="form-control" required>
    <span id="login-captcha-error" class="text-danger"></span>
</div>

<div class="text-center">
    <button type="submit" class="btn btn-success">Login Now</button>
</div>

</form>

<div id="socialLinks" class="text-center mt-2"></div>
</div>
</div>
</div>
</div>

<!-- REGISTER MODAL -->
<div class="modal fade" id="myRegisterModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<div class="backgroud-style text-center">
    <h2>Register</h2>
    <button type="button" class="close modal_close" data-dismiss="modal">×</button>
</div>

<div class="modal-body">
<form id="registerForm" method="POST" action="{{ route('frontend.auth.register.post') }}">
@csrf

<input type="text" name="first_name" class="form-control mb-2" placeholder="First Name">
<input type="text" name="last_name" class="form-control mb-2" placeholder="Last Name">
<input type="email" name="email" class="form-control mb-2" placeholder="Email">
<input type="password" name="password" class="form-control mb-2" placeholder="Password">
<input type="password" name="password_confirmation" class="form-control mb-2" placeholder="Confirm Password">

<!-- CAPTCHA -->
<div class="contact-info mb-2">
    <label>Captcha: <strong id="register-captcha-question"></strong></label>
    <input type="text" name="captcha" class="form-control" required>
    <span id="captcha-error" class="text-danger"></span>
</div>

<div class="text-center">
    <button type="submit" class="btn btn-success">Register Now</button>
</div>

</form>
</div>
</div>
</div>
</div>

@endif

@push('after-scripts')
<script>
$(document).on('click', '#openLoginModal', function () {
    $.get("{{ route('frontend.auth.login') }}", function (res) {
        $('#socialLinks').html(res.socialLinks);
        $('#login-captcha-question').text(res.captcha_question);
        $('#myModal').modal('show');
    });
});

$(document).on('click', '#openRegisterModal', function () {
    $.get("{{ route('frontend.auth.register') }}", function (res) {
        $('#register-captcha-question').text(res.captcha_question);
        $('#myRegisterModal').modal('show');
    });
});
</script>
@endpush
