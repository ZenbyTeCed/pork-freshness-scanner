<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FirebaseGuest
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->session()->has('firebase_uid')) {
            return redirect()->intended(route('scan'));
        }

        return $next($request);
    }
}
