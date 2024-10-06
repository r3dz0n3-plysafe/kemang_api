<?php

namespace App\Http\Middleware;

use App\Traits\ResponseApi;
use Closure;

class ApiResponseMiddleware
{
    use ResponseApi;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $original = $response->getData();

            if ($response->status() < 400) {
                $response->setData($this->success($original->message ?? '', $original, $response->status()));
            } else {
                $response->setData($this->error($original->message ?? '', $original, $response->status()));
            }
        }

        return $response;
    }
}
