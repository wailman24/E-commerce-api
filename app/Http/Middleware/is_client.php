<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class is_client
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $roleid = $user->role_id;
        $role = DB::table('roles')->where('id', $roleid)->first();
        if ($role->name != 'client') {
            return response()->json([
                'status' => false,
                'message' => 'your are not allowed'
            ], 405);
        }
        return $next($request);
    }
}
