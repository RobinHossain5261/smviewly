<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockSearchIndexing
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldHide($request)) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        }

        return $response;
    }

    private function shouldHide(Request $request): bool
    {
        return $request->is(
            'admin',
            'admin/*',
            'signin',
            'signup',
            'search',
            'search-preview',
            'media',
            'download',
            'reload-captcha',
            'up',
        );
    }
}
