<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidatePublicPagination
{
    public function handle(Request $request, Closure $next): Response
    {
        $listing = $request->routeIs('properties.index', 'projects.index', 'agents.index', 'blog.index', 'careers.index');
        if ($listing && $request->query->has('page')) {
            $page = $request->query('page');
            abort_unless(is_scalar($page) && preg_match('/^[1-9][0-9]*$/D', (string) $page) && strlen((string) $page) < 10, 404);
        }

        return $next($request);
    }
}
