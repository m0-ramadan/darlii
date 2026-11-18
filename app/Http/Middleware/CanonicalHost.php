<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->getHost() === 'www.darlii.com' && in_array($request->method(), ['GET', 'HEAD'], true)) {
            return redirect()->away('https://darlii.com' . $request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
