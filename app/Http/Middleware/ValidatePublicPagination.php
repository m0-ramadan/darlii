<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
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

        $response = $next($request);
        if ($listing && method_exists($response, 'getOriginalContent')) {
            $view = $response->getOriginalContent();
            if ($view instanceof View) {
                foreach ($view->getData() as $data) {
                    if ($data instanceof LengthAwarePaginator) {
                        abort_if($data->currentPage() > $data->lastPage(), 404);
                    }
                }
            }
        }

        return $response;
    }
}
