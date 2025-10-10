<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Handle an authentication attempt.
     * @param $request
     * @return RedirectResponse
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
 
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('bingo.dial');
        }
 
        return back()->withErrors([
            'Fail to login',
        ]);
    }

    /**
     * View login
     *
     * @return View
     */
    public function index(): View
    {
        return view('login');
    }
}
