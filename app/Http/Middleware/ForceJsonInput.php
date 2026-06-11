<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonInput
{
    public function handle(Request $request, Closure $next)
    {
        $contentType = $request->header('Content-Type', '');
        if (str_contains($contentType, 'application/json') && $request->getMethod() !== 'GET') {
            $raw = $request->getContent();
            if ($raw) {
                $json = json_decode($raw, true);
                if (is_array($json)) {
                    $request->merge($json);
                }
            }
        }
        return $next($request);
    }
}
