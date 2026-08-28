<?php
namespace App\Http\Controllers;
use App\Models\User;use Illuminate\Http\Request;use Illuminate\Support\Facades\Auth;
class AuthController extends Controller{
 public function showLogin(){return view('auth.login');} public function showRegister(){return view('auth.register');}
 public function register(Request $r){$d=$r->validate(['name'=>'required|string|max:255','email'=>'required|email|max:255|unique:users,email','password'=>'required|string|min:8|confirmed']);$u=User::create($d);Auth::login($u);$r->session()->regenerate();return redirect()->route('dashboard');}
 public function login(Request $r){$c=$r->validate(['email'=>'required|email','password'=>'required|string']);if(!Auth::attempt($c,$r->boolean('remember')))return back()->withErrors(['email'=>'The provided credentials are incorrect.'])->onlyInput('email');$r->session()->regenerate();return redirect()->intended(route('dashboard'));}
 public function logout(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('login');}
}
