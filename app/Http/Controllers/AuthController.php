<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('pages.login');
    }

    public function showRegister()
    {
        return view('pages.register');
    }

    public function login(Request $request)
    {
        $request->validate([
            'uid' => 'required|string',
            'email' => 'nullable|email',
            'name' => 'nullable|string',
        ]);

        session([
            'firebase_uid' => $request->uid,
            'firebase_email' => $request->email,
            'firebase_name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard'),
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'uid' => 'required|string',
            'email' => 'nullable|email',
            'name' => 'nullable|string',
        ]);

        session([
            'firebase_uid' => $request->uid,
            'firebase_email' => $request->email,
            'firebase_name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard'),
        ]);
    }

    public function googleLogin(Request $request)
    {
        $request->validate([
            'uid' => 'required|string',
            'email' => 'nullable|email',
            'name' => 'nullable|string',
        ]);

        session([
            'firebase_uid' => $request->uid,
            'firebase_email' => $request->email,
            'firebase_name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard'),
        ]);
    }

    public function logout(Request $request)
    {

    \Log::info('logout hit');
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
        ]);
    }
}