<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // default
        return parent::render($request, $exception);

        // if (!$request->is('api/*')) {
        //   if ($this->isHttpException($exception)) {
        //     if ($exception->getStatusCode() == 404) {
        //       return response()->view('errors.404', [], 404);
        //     } else if ($exception->getStatusCode() == 500) {
        //       return response()->view('errors.500', [], 500);
        //     } else {
        //       return response()->view('errors.other', [
        //         'code' => $exception->getStatusCode(),
        //         'message' => $exception->getMessage(),
        //       ], $exception->getStatusCode());
        //     }
        //   } else if ($exception instanceof \Illuminate\Database\QueryException) {
        //     return response()->view('errors.other', [
        //       'code' => 500,
        //       'message' => $exception->getMessage(),
        //     ], 500);
        //   } else if ($exception instanceof \ErrorException) {
        //     return response()->view('errors.other', [
        //       'code' => 500,
        //       'message' => $exception->getMessage(),
        //     ], 500);
        //   } else if ($exception instanceof \Exception) {
        //     return response()->view('errors.other', [
        //       'code' => 500,
        //       'message' => $exception->getMessage(),
        //     ], 500);
        //   } else if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
        //     return response()->view('errors.404', [], 404);
        //   } else if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
        //     return response()->view('errors.other', [
        //       'code' => 405,
        //       'message' => $exception->getMessage(),
        //     ], 405);
        //   } else if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
        //     return response()->view('errors.other', [
        //       'code' => $exception->getStatusCode(),
        //       'message' => $exception->getMessage(),
        //     ], $exception->getStatusCode());
        //   } else if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
        //     return response()->view('errors.other', [
        //       'code' => 401,
        //       'message' => $exception->getMessage(),
        //     ], 401);
        //   } else if ($exception instanceof \Illuminate\Validation\ValidationException) {
        //     return response()->view('errors.other', [
        //       'code' => 422,
        //       'message' => $exception->getMessage(),
        //     ], 422);
        //   } else if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
        //     return response()->view('errors.other', [
        //       'code' => 419,
        //       'message' => $exception->getMessage(),
        //     ], 419);
        //   } else if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
        //     return response()->view('errors.other', [
        //       'code' => 403,
        //       'message' => $exception->getMessage(),
        //     ], 403);
        //   } else if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
        //     return response()->view('errors.other', [
        //       'code' => 404,
        //       'message' => $exception->getMessage(),
        //     ], 404);
        //   } else {
        //     return parent::render($request, $exception);
        //   }
        // } else {
        //   return parent::render($request, $exception);
        // }
    }
}
