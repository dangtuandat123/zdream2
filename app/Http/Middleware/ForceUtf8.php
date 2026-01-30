<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceUtf8
{
    /**
     * Ensure all HTML responses use UTF-8.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $contentType = $response->headers->get('Content-Type', '');
        if ($contentType !== '' && !str_contains(strtolower($contentType), 'charset')) {
            $response->headers->set('Content-Type', $contentType . '; charset=UTF-8');
        }

        return $response;
    }
}
