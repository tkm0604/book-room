<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ログイン済みで、メールが未認証の場合はリダイレクト
        if(Auth::check() && is_null(Auth::user()->email_verified_at)){
            return redirect('/verify-email');
        }
        
        return $next($request);
    }
}