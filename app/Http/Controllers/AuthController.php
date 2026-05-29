<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
public function showLogin()
{
return view('auth.login');
}
public function login(Request $request)
{
$credentials = $request->validate([
'email' => ['required', 'email'],
'password' => ['required']
]);

if (Auth::attempt($credentials)) {
$request->session()->regenerate();
return redirect()->route('dashboard')->with('success', 'Bienvenido al sistema');
}
return back()->withErrors([
'email' => 'Las credenciales no son correctas.',
])->onlyInput('email');
}
public function dashboard()
{
return view('dashboard');
}
public function logout(Request $request)
{
Auth::logout();
$request->session()->invalidate();
$request->session()->regenerateToken();
return redirect()->route('login.form')->with('success', 'Sesión cerrada
correctamente');
}
}