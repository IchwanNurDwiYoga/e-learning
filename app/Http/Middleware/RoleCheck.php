<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Jika user belum login
        if (!$request->user()) {
            return redirect('login');
        }

        // Jika tidak ada role yang dispesifikasikan, izinkan semua
        if (empty($roles)) {
            return $next($request);
        }

        // Jika user tidak memiliki salah satu dari role yang sesuai
        if (!$request->user()->hasRole($roles)) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
