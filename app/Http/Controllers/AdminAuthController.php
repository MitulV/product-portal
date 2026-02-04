<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
  public function create()
  {
    return view('admin.auth.login');
  }

  public function store(Request $request)
  {
    $credentials = $request->validate([
      'email' => ['required', 'email'],
      'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
      $request->session()->regenerate();

      return redirect()->intended(route('admin.dashboard'));
    }

    return back()->withErrors([
      'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
  }

  public function destroy(Request $request)
  {
    Auth::logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return redirect('/');
  }

  public function showChangePassword()
  {
    return view('admin.auth.change-password');
  }

  public function updatePassword(Request $request)
  {
    $request->validate([
      'password' => ['required', 'min:8', 'confirmed'],
    ]);

    /** @var \App\Models\User $user */
    $user = Auth::user();
    $user->password = Hash::make($request->password);
    $user->save();

    return redirect()->route('admin.dashboard')->with('success', 'Password updated successfully.');
  }
}
