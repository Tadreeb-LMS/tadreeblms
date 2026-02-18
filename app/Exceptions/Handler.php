<?php

namespace App\Exceptions;

use Throwable;
use ErrorException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\TokenMismatchException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
// use App\Notifications\Backend\SystemNotification;
// use App\Services\NotificationSettingsService;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Register exception handling callbacks.
     */
    public function register()
    {
        $this->renderable(function (ErrorException $e, $request) {
            if (str_contains($e->getMessage(), 'Attempt to read property')) {

                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Requested data not found'
                    ], 404);
                }

                abort(404, 'Requested data not found');
            }
        });
    }

    public function render($request, Throwable $exception)
    {
        /* ✅ CSRF TOKEN MISMATCH (419) */
        if ($exception instanceof TokenMismatchException) {

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your session has expired. Please refresh the page and try again.'
                ], 419);
            }

            return redirect()
                ->back()
                ->withInput($request->except('_token'))
                ->withErrors([
                    'session' => 'Your session expired. Please refresh the page and try again.'
                ]);
        }

        /* ✅ PERMISSION ERROR */
        if ($exception instanceof UnauthorizedException) {
            return redirect()
                ->route(home_route())
                ->withFlashDanger(__('auth.general_error'));
        }

        /* ✅ POST TOO LARGE ERROR */
        if ($exception instanceof \Illuminate\Http\Exceptions\PostTooLargeException) {
            return redirect()
                ->back()
                ->withFlashDanger(__('The uploaded file is too large. Please upload a smaller file.'));
        }

        return parent::render($request, $exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
            ? response()->json(['message' => 'Unauthenticated.'], 401)
            : redirect()->guest(route('frontend.auth.login'));
    }
}
