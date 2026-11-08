<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFinancePointsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('finance.points_enabled', false)) {
            return redirect()->route('admin.finance.index')
                ->with('warning', 'بند النقاط متوقف حاليًا.');
        }

        return $next($request);
    }
}
