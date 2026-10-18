<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gzip-compresses text responses (HTML, JSON, XML, CSS, JS, SVG) when the
 * client supports it. Static assets are compressed by the built-in server
 * router in server.php; this middleware covers Laravel responses.
 */
class CompressResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $accept = strtolower((string) $request->headers->get('Accept-Encoding', ''));
        if (! str_contains($accept, 'gzip') || $response->headers->has('Content-Encoding')) {
            return $response;
        }

        $content = $response->getContent();
        if (! is_string($content) || strlen($content) < 1024) {
            return $response;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));
        $isText = $contentType === ''
            || str_contains($contentType, 'text/')
            || str_contains($contentType, 'application/json')
            || str_contains($contentType, 'application/javascript')
            || str_contains($contentType, 'application/xml')
            || str_contains($contentType, 'image/svg+xml');

        if (! $isText) {
            return $response;
        }

        $compressed = gzencode($content, 6);
        if ($compressed === false) {
            return $response;
        }

        $response->setContent($compressed);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Vary', 'Accept-Encoding');
        $response->headers->remove('Content-Length');

        return $response;
    }
}
