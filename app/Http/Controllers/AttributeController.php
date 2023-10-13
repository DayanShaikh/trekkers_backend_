<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attribute;

class AttributeController extends Controller
{
    public function getAttribute()
    {
        $attributes = Attribute::where('status',1)->get();
        if($attributes){
            return response()->json($attributes);
        }
        return response()->json(['error' => 'No Record Found'], 422);
    }
}
