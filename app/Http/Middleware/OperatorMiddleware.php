<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OperatorMiddleware
{
  public function handle($request, Closure $next)
{
    if (auth()->user()->role !== 'operator') {
        abort(403);
    }

    return $next($request);
}

}
