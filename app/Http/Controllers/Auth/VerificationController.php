<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerificationController extends Controller
{

	/**
	 * Send Verify Email
	 *
	 * This endpoint allows you to send verification email.
	 */
	public function sendNotification(Request $request)
	{
		$request->user()->sendEmailVerificationNotification();

		return response()->json([
			'status' => true,
			'message' => 'Verification link sent!',
		]);
	}

	/**
	 * Verify Email
	 *
	 * This endpoint allows you to verify email according to previous endpoint.
	 */
    public function verify(EmailVerificationRequest $request)
	{
		$request->fulfill();

		return response()->json([
			'status' => true
		]);
	}
}
