<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserIsVerifiedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(!$request->user()->hasVerifiedEmail())
        {
            return response()->json([
                'status'  => false,
                'message' => 'Please verify your email before proceeding.'
            ], 200);
        }
        return $next($request);
    }
}
