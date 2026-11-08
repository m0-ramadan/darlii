<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user && $user->isPrimarySuperAdmin(), 403, 'هذا القسم مخصص للسوبر أدمن الأساسي فقط.');

        return $next($request);
    }
}
