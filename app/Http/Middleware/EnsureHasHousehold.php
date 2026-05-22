<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasHousehold
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->currentHouseholdMember) {
            return redirect()->route('household.create');
        }

        return $next($request);
    }
}
