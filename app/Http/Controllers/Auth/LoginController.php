<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use DB;

class LoginController extends Controller
{
	/**
	 * Login User
	 *
	 */
	public function authenticate(Request $request)
	{
		$credentials = $request->validate([
			'email' => ['required', 'email'],
			'password' => ['required'],
		]);
	
		if (Auth::attempt($credentials)) {
			$request->session()->regenerate();
			// return $credentials;
			User::find(auth()->user()->id)->update([
				'last_logged_in_at' => \Carbon\Carbon::now()
			]);
			

			return response()->json([
				'status' => true,
				'user' => auth()->user(),
				// "token" =>  JWTAuth::attempt($credentials),
			]);
		}

		return response()->json([
			'errors' =>
				[
					'general' => ['Verkeerde e-mail of wachtwoord.']
				]
		], 422);
	}


	/**
	 * Logs out current user
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function logout(Request $request)
	{
		$request->session()->invalidate();

		$request->session()->regenerateToken();

		return response()->json(['status' => true]);
	}
}
