<?php

dd('auth.php loaded');

use App\Http\Controllers\Frontend\Auth\LoginController;
use App\Http\Controllers\Frontend\Auth\RegisterController;
use App\Http\Controllers\Frontend\Auth\SocialLoginController;
use App\Http\Controllers\Frontend\Auth\ResetPasswordController;
use App\Http\Controllers\Frontend\Auth\ConfirmAccountController;
use App\Http\Controllers\Frontend\Auth\ForgotPasswordController;
use App\Http\Controllers\Frontend\Auth\UpdatePasswordController;
use App\Http\Controllers\Frontend\Auth\PasswordExpiredController;
use App\Http\Controllers\Frontend\Auth\TeacherRegisterController;

/*
 * Frontend Access Controllers
 * All route names are prefixed with 'frontend.auth'.
 */
Route::group(['namespace' => 'Auth', 'as' => 'frontend.auth.'], function () {

    /*
    * These routes require the user to be logged in
    */
    Route::group(['middleware' => 'auth'], function () {
        Route::get('logout', [LoginController::class, 'logout'])->name('logout');


        //For when admin is logged in as user from backend
        Route::get('logout-as', [LoginController::class, 'logoutAs'])->name('logout-as');

        // These routes can not be hit if the password is expired
        Route::group(['middleware' => 'password_expires'], function () {
            // Change Password Routes
            Route::patch('password/update', [UpdatePasswordController::class, 'update'])->name('password.update');
        });

        // Password expired routes
        if (is_numeric(config('access.users.password_expires_days'))) {
            Route::get('password/expired', [PasswordExpiredController::class, 'expired'])->name('password.expired');
            Route::patch('password/expired', [PasswordExpiredController::class, 'update'])->name('password.expired.update');
        }
    });

    /*
     * These routes require no user to be logged in
     */
    Route::group(['middleware' => 'guest'], function () {
        // Authentication Routes
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login'])->name('login.post');

        // Socialite Routes
        Route::get('login/{provider}', [SocialLoginController::class, 'login'])->name('social.login');
        Route::get('login/{provider}/callback', [SocialLoginController::class, 'login']);

        // Registration Routes
        if (config('access.registration')) {
            Route::get('register', [LoginController::class, 'showLoginForm'])->name('register');
            Route::post('register', [RegisterController::class, 'register'])->name('register.post');
        }

        // Confirm Account Routes
        Route::get('account/confirm/{token}', [ConfirmAccountController::class, 'confirm'])->name('account.confirm');
        Route::get('account/confirm/resend/{uuid}', [ConfirmAccountController::class, 'sendConfirmationEmail'])->name('account.confirm.resend');
        Route::get('verify/{token}', [RegisterController::class, 'verifyUser']);
        // Password Reset Routes
        Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.email');
        Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email.post');

        Route::get('change-password/{token}', [ForgotPasswordController::class, 'changePassword'])->name('password.email');
        Route::post('change-password', [ForgotPasswordController::class, 'changePasswordPost'])->name('password.email');


        Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset.form');
        Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.reset');

        // New Register Teacher Routes
        Route::get('teacher/register',[TeacherRegisterController::class, 'showTeacherRegistrationForm'])->name('teacher.register');
        Route::post('teacher/register', [TeacherRegisterController::class, 'register'])->name('teacher.register.post');

    });
});
