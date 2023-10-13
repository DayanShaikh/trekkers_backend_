<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FrontMenu;

class FrontMenuController extends Controller
{
	public function frontMenu()
	{
		$frontMenus = FrontMenu::get();
		if($frontMenus){
			return response()->json($frontMenus);
		}
		return response()->json(['error' => 'No Record Found'], 422);
	}
}
