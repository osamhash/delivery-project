<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
   public function handle($request, Closure $next)
    {
        $user = auth()->user();

        // role_id = 2 للآدمن
        if (!$user || $user->role_id !== 2) {
            return response()->json([
                'status'  => false,
                'message' => 'غير مصرح — هذه الصفحة للمسؤولين فقط'
            ], 403);
        }

        return $next($request);
    }
}
