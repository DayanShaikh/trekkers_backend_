<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getAuthUser()
    {
        return response()->json([
            "user" => auth()->user()
        ]);
    }
}
