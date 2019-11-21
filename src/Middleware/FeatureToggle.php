<?php

declare(strict_types=1);

namespace FeatureToggle\Middleware;

use Closure;
use Illuminate\Http\Request;

class FeatureToggle
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $name
     * @param  string|int|bool  $checkActive
     * @param  int  $abort
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $name, $checkActive = true, $abort = 404)
    {
        if (! feature_toggle($name, $checkActive)) {
            return abort($abort);
        }

        return $next($request);
    }
}
