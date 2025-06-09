<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Redirect users after successful login based on their role and status.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->hasRole('user')) {
            if ($user->status === 'freezed') {
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'error' => 'Your account is currently frozen. Please contact support.',
                ]);
            }
            else if ($user->status === 'banned') {
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'error' => 'Your account is banned. Please contact support.',
                ]);
            }
            else return redirect()->route('home');
        }

        if ($user->hasRole('admin')) {
            return redirect()->route('home');
        }
        return redirect()->route('home');
    }
}
