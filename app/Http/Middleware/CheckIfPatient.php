<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckIfPatient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Auth::User()->role!=='Patient'){
            return $request->wantsJson()
                ? new JsonResponse([], 204)
                : redirect(RouteServiceProvider::HOME)->with('warning',"You don't have the authority for this action. This is allowed only for the Patient.");
        }

        return $next($request);
    }
}
