<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request; // Correctly import the Request class

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    // Correctly type-hinted authenticated method
    protected function authenticated(Request $request, $user)
    {
        if ($user->role == 'Doctor') {
            return redirect()->route('doctor.dashboard');
        } elseif ($user->role == 'Patient') {
            return redirect()->route('diary');
        }

        return redirect($this->redirectTo); // Fallback to the default home
    }
}
