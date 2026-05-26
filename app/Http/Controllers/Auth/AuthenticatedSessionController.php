<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | AUTHENTICATE USER
        |--------------------------------------------------------------------------
        */

        $request->authenticate();

        /*
        |--------------------------------------------------------------------------
        | REGENERATE SESSION
        |--------------------------------------------------------------------------
        */

        $request->session()->regenerate();

        /*
        |--------------------------------------------------------------------------
        | CURRENT USER
        |--------------------------------------------------------------------------
        */

        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        if (
            $user->role &&
            strtolower($user->role->name) === 'admin'
        ) {

            return redirect('/dashboard');
        }

        /*
        |--------------------------------------------------------------------------
        | MANAGER
        |--------------------------------------------------------------------------
        */

        if (
            $user->role &&
            strtolower($user->role->name) === 'manager'
        ) {

            return redirect('/manager');
        }

        /*
        |--------------------------------------------------------------------------
        | EMPLOYEE
        |--------------------------------------------------------------------------
        */

        if (
            $user->role &&
            strtolower($user->role->name) === 'employee'
        ) {

            /*
            |--------------------------------------------------------------------------
            | TOILET EMPLOYEE
            |--------------------------------------------------------------------------
            */

            if ($user->toilet) {

                /*
                |--------------------------------------------------------------------------
                | STENDI
                |--------------------------------------------------------------------------
                */

                if (
                    strtolower($user->toilet->name) === 'stendi'
                ) {

                    return redirect()->route(
                        'stendi.dashboard'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | SOKONI
                |--------------------------------------------------------------------------
                */

                if (
                    strtolower($user->toilet->name) === 'sokoni'
                ) {

                    return redirect()->route(
                        'sokoni.dashboard'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | NORMAL EMPLOYEE
            |--------------------------------------------------------------------------
            */

            return redirect('/employee');
        }

        /*
        |--------------------------------------------------------------------------
        | DEFAULT
        |--------------------------------------------------------------------------
        */

        return redirect('/');
    }

    /**
     * Logout.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}