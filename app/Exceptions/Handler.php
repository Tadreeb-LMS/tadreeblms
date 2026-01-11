<?php

namespace App\Exceptions;

use Throwable;
use ErrorException;
use Illuminate\Auth\AuthenticationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
        if ($exception instanceof UnauthorizedException) {
            return redirect()
                ->route(home_route())
                ->withFlashDanger(__('auth.general_error'));
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
