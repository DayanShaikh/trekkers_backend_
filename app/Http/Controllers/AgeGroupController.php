<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Location;
use App\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\AgeGroup;
use Illuminate\Support\Str;

class AgeGroupController extends Controller
{
    public function ageGroups()
    {
        $ageGroups = AgeGroup::with('page')->get();
        if($ageGroups){
            return response()->json($ageGroups);
        }
        return response()->json(['error' => 'No Record Found'], 422);
	}
	
	public function ageGroup(AgeGroup $ageGroup, Request $request){
		if($request->get("month")){
			$response = Location::when($request->has('attribute_id'), function($q) use($request){
				$q->whereHas("attributes", function($query) use($request){
					$query->where('attribute_id', '=', $request->get('attribute_id'));
				});
			})
			->whereHas("trips", function($q) use($month_start, $month_end, $request){
				$q->whereHas("ageGroups", function($query) use($request){
					$query->where("age_group_id", "=", $request->get("age_group_id"));
				})->whereBetween('start_date', [$month_start->format('Y-m-d'), $month_end->format('Y-m-d')]);
			})
			->get();
		}
		else{
			$response = Attribute::with([
				"locations" => function($query) use($ageGroup){
					$query->with([
						"locationAgeGroups" => function($query) use($ageGroup){
							$query->where("age_group_id", "=", $ageGroup->id);
						}
					])->whereHas("locationAgeGroups", function($query) use($ageGroup){
						$query->where("age_group_id", "=", $ageGroup->id);
					})->where("status", 1)->orderBy("sortorder");
				}
			])->orderBy("sortorder")->get();
		}
		return $response;
	}
}
