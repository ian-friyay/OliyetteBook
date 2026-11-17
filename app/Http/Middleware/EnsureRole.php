<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->guest(route('login') . '?next=' . urlencode($request->getRequestUri()));
        }

        if (! in_array(Auth::user()->role, $roles, true)) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
