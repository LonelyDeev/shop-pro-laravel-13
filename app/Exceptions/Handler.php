<?php

namespace App\Exceptions;

use App\Http\Traits\Helpers\ApiResponseTrait;
use BadMethodCallException;
use ErrorException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponseTrait;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        // ✅ اگر درخواست JSON است
        if ($request->expectsJson()) {
            return $this->renderJsonException($request, $exception);
        }

        // ✅ اگر تابع highlight_file وجود ندارد
        if (!function_exists('highlight_file')) {
            return $this->renderSimpleError($request, $exception);
        }

        // ✅ اگر خطای Facade root رخ داده است
        if ($exception instanceof \RuntimeException &&
            str_contains($exception->getMessage(), 'facade root has not been set')) {
            return $this->renderSimpleError($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Render JSON response for exceptions
     */
    protected function renderJsonException($request, Throwable $exception)
    {
        // اگر خطای Facade root باشد
        if ($exception instanceof \RuntimeException &&
            str_contains($exception->getMessage(), 'facade root has not been set')) {
            return response()->json([
                'success' => false,
                'message' => 'Application is not properly initialized. Please clear cache.',
            ], 500);
        }

        if ($exception instanceof PostTooLargeException) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => "Size of attached file should be less " . ini_get("upload_max_filesize") . "B"
                ],
                413
            );
        }

        if ($exception instanceof AuthenticationException) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => 'Unauthenticated or Token Expired, Please Login'
                ],
                401
            );
        }

        if ($exception instanceof ThrottleRequestsException) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => 'Too Many Requests, Please Slow Down'
                ],
                429
            );
        }

        if ($exception instanceof ModelNotFoundException) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => 'Entry for ' . str_replace('App\\Models\\', '', $exception->getModel()) . ' not found'
                ],
                404
            );
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => 'Page not found'
                ],
                404
            );
        }

        if ($exception instanceof ValidationException) {
            return $this->apiResponse(
                [
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'errors' => $exception->errors()
                ],
                422
            );
        }

        if ($exception instanceof AuthorizationException) {
            return $this->respondForbidden();
        }

        if ($exception instanceof QueryException) {
            $errorData = [
                'success' => false,
                'message' => 'There was Issue with the Query'
            ];

            if (config('app.debug')) {
                $errorData['exception'] = $exception;
            }

            return $this->apiResponse($errorData, 500);
        }

        if ($exception instanceof ErrorException ||
            $exception instanceof \Error ||
            $exception instanceof BadMethodCallException) {

            $errorData = [
                'success' => false,
                'message' => "There was some internal error"
            ];

            if (config('app.debug')) {
                $errorData['exception'] = $exception;
            }

            return $this->apiResponse($errorData, 500);
        }

        // برای سایر استثناها
        $errorData = [
            'success' => false,
            'message' => $exception->getMessage() ?: 'An error occurred'
        ];

        if (config('app.debug')) {
            $errorData['exception'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        return $this->apiResponse($errorData, 500);
    }

    /**
     * Render simple error without using Facade
     */
    protected function renderSimpleError($request, Throwable $exception)
    {
        $message = $exception->getMessage() ?: 'An unexpected error occurred';
        $code = $this->isHttpException($exception) ? $exception->getStatusCode() : 500;

        // ساده‌ترین HTML ممکن بدون استفاده از Facade
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error {$code}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            max-width: 600px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #dc3545; font-size: 48px; margin: 0; }
        p { color: #333; margin: 20px 0; }
        .message {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            color: #666;
            word-break: break-word;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        a:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>{$code}</h1>
        <p>Something went wrong</p>
        <div class="message">{$message}</div>
        <a href="/">Go back home</a>
        <p style="font-size:12px; color:#999; margin-top:30px;">
            Please clear application cache to fix this issue.
        </p>
    </div>
</body>
</html>
HTML;

        return response($html, $code);
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, \Illuminate\Validation\ValidationException $exception)
    {
        return $this->apiResponse(
            [
                'success' => false,
                'message' => $exception->getMessage(),
                'errors' => $exception->errors()
            ],
            422
        );
    }
}
