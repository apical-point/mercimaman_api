<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class RequestResponseLogMiddleware
{
    public function handle($request, Closure $next)
    {
        $uniqid = uniqid();
        $this->logRequest($uniqid, $request);

        $response = $next($request);

        $this->logResponse($uniqid, $response);

        return $response;
    }



    /**
     * Log request
     *
     * @param string $uniqid
     * @param \Illuminate\Http\Request $request https://laravel.com/api/5.7/Illuminate/Http/Request.html
     */
    private function logRequest($uniqid, $request)
    {
        Log::info($uniqid, [
            // "header" => $request->header(),
            "method" => $request->method(),
            "path" => $request->fullUrl(),
            "input" => $this->blockElement($request->all()),
            "user_id" => ($request->user())?$request->user()->id:"",
        ]);
    }

    /**
     * Log response
     *
     * @param string $uniqid
     * @param \Illuminate\Http\Response $response https://laravel.com/api/5.7/Illuminate/Http/Response.html
     */
    private function logResponse($uniqid, $response)
    {

        if(get_class($response)=='Illuminate\Http\JsonResponse'){
            Log::info($uniqid, [
                //"header" => $response->headers,
                "status" => $response->status(),
                "exception" => $response->exception,
                //"content" => $response->content(),
            ]);
        }else{
            Log::info($uniqid, [
                "header" => $response->headers,
                //"status" => $response->status(),
                //"exception" => $response->exception,
                //"content" => $response->content(),
            ]);

         }
    }

    private function blockElement($data){
        $retVal=$data;
        unset($retVal['password']);
        unset($retVal['current_password']);
        unset($retVal['password_confirm']);
        return $retVal;
    }
}
