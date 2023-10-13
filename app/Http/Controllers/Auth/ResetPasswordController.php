<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Models\TripBooking;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\ResetPassword;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Utility;
use Hash;

class ResetPasswordController extends Controller
{
	/**
	 * Request a Password Reset
	 *
	 * This endpoint allows you to request password reset for email.
	 */
    public function sendResetToken(Request $request)
	{
		$request->validate(['email' => 'required|email']);

		$user = User::whereEmail($request->email)->first();
		$booking = TripBooking::select('id')->where(['user_id' => $user->id, 'status' => 1, 'deleted' => 0])->orderBy('created_at', 'desc')->first();
		if(!$user){
			return response()->json([
				'message' => 'Email not found'
			], 422);
		}

		DB::delete('delete from password_resets where email = :email', ['email' => $request->email]);

		$token = random_int(100000, 999999);

		DB::table('password_resets')->insert([
			'email' => $request->email,
			'token' => $token,
			'created_at' => Carbon::now()
		]);

		//$user->sendPasswordResetNotification($token);
		//Mail::to($request->email)->send(new ResetPassword($token));
		$subject = Utility::getConfig('password_email_subject')->value;
		$body = Utility::getConfig('password_email_body')->value;
		$body=str_replace(
			[
				'[$password]',
			],
			[
				$token
			],
			$body
		);
		Utility::send_email($request->email, $subject, $body, $booking->id);

		return response()->json(['status' => true, 'message' => 'Reset token has been sent']);
	}

	/**
	 * Verify Reset Token
	 *
	 * This endpoint allows you to verify token which was sent previously.
	 */
	public function verifyToken(Request $request)
	{
		$request->validate([
			'email' => 'required|email',
			'token' => 'required', 'integer', 'size:6'
		]);

		$user = User::whereEmail($request->email)->first();
		if(!$user){
			return response()->json([
				'errors' => [
					'email' => ['Email not found']
				]
			], 422);
		}

		$doesExists = count(DB::select('select * from password_resets where email = :email and token = :token', ['email' => $request->email, 'token' => $request->token])) > 0;
		if(!$doesExists){
			return response()->json([
				'errors' => [
					'token' => ['Incorrect token.']
				]
			], 422);
		}

		return response()->json(['status' => true]);
	}

	/**
	 * Reset Password
	 *
	 * This endpoint allows you to reset password according to previous endpoint.
	 */
	public function reset(Request $request)
	{
		$request->validate([
			'token' => ['required'],
			'email' => ['required', 'email'],
			'password' => ['required', 'min:8'],
		]);

		$user = User::whereEmail($request->email)->first();
		if(!$user){
			return response()->json([
				'errors' => [
					'token' => ['Email not found.']
				]
			], 422);
		}

		$doesExists = count(DB::select('select * from password_resets where email = :email and token = :token', ['email' => $request->email, 'token' => $request->token])) > 0;
		if(!$doesExists){
			return response()->json([
				'errors' => [
					'token' => ['Incorrect token.']
				]
			], 422);
		}

		DB::delete('delete from password_resets where email = :email', ['email' => $request->email]);

		$user->forceFill([
			'password' => Hash::make($request->password)
		])->setRememberToken(Str::random(60));

		$user->save();

		event(new PasswordReset($user));

		return response()->json(['status' => true, 'message' => 'Password has been reset successfully']);
	}
}
