<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

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

    public function render($request, Throwable $e)
    {
        if ($request->is('api/*') && $request->wantsJson()) {
//            $request->headers->set('Accept', 'application/json');
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    private function handleApiException(Request $request, Throwable $e)
    {
        $httpCode = array_keys(Response::$statusTexts);
        if (!in_array($e->getCode(), $httpCode)) {
            $statusCode = 500;
        } else {
            $statusCode = $e->getCode();
        }
        $response = ['message' => $e->getMessage(), 'error_code' => $e->getCode(), 'throwable' => get_class($e)];
        if ($e instanceof TokenInvalidException) {
            return response()->json($response, $statusCode);
        }

        if ($e instanceof TokenExpiredException) {
            return response()->json($response, $statusCode);
        }

        if ($e instanceof QueryException) {
            return response()->json($response, $statusCode);
        }

        if ($e instanceof CustomException) {
            return response()->json($response, $statusCode);
        }

        $e = $this->prepareException($e);

        if ($e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
            $e = $e->getResponse();
        }

        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            $e = $this->unauthenticated($request, $e);
        }

        if ($e instanceof \Illuminate\Validation\ValidationException) {
            $e = $this->convertValidationExceptionToResponse($e, $request);
        }

        return $this->customApiResponse($e);
    }

    private function customApiResponse($e)
    {
        if (method_exists($e, 'getStatusCode')) {
            $statusCode = $e->getStatusCode();
        } else {
            $statusCode = 500;
        }

        $response = [];
        $response['message'] = \Symfony\Component\HttpFoundation\Response::$statusTexts[$statusCode];
        try {
            if ($extMsg = json_decode($e->getMessage(), true)) {
                $response['extend_message'] = $extMsg;
            } else {
                $response['extend_message'] = $e->getMessage();
            }
        } catch (\Exception $exception) {
            $response['extend_message'] = null;
        }
        if ($statusCode == 422) {
            $response['message'] = 'Pastikan Anda mengisi form dengan benar!';
            $response['errors'] = $e->original['errors'];
            return response()->json($response, $statusCode);
        }
        if ($statusCode == 401) {
            $response['message'] = 'Unauthorized';
            $response['code'] = 401;
            return response()->json($response, $statusCode);
        }

        if (config('app.debug')) {
            $response['exception'] = get_class($e);
            $response['trace'] = $e->getTrace();
            $response['code'] = $e->getCode() ?? $statusCode;
        }

        $response['status'] = $statusCode;

        return response()->json($response, $statusCode);
    }
}
