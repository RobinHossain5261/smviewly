<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePrimaryHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $primaryHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (! $primaryHost || strcasecmp($request->getHost(), $primaryHost) === 0) {
            return $next($request);
        }

        $target = rtrim((string) config('app.url'), '/').$request->getRequestUri();

        return redirect()->away($target, 301);
    }
}
