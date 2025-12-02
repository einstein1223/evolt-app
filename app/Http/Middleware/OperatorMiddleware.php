<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class OperatorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek 1: Apakah user sudah login?
        // Cek 2: Apakah role user adalah 'operator'?
        if (Auth::check() && Auth::user()->role === 'operator') {
            return $next($request); // Silakan masuk, Pak Operator.
        }

        // Jika bukan operator, tendang ke Dashboard User biasa
        return redirect('/dashboard'); 
    }
}