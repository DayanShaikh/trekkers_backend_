<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Destination;
use App\Models\AgeGroup;
use Carbon\Carbon;

class DestinationController extends Controller
{
    public function getLocationByAgeGroup(Request $request)
    {
        $location = Destination::with(['locations' => function($query) use($request){
            $query->when($request->has('age_group'), function ($q) use($request){
                $q->whereHas('locationAgeGroups', function ($query) use($request){
                    $query->where("age_group_id", $request->get('age_group'));
                });
            });
        }, 'locations.locationAgeGroups' => function ($q) use($request){
				$q->when($request->has('age_group'), function ($query) use($request){
					$query->where("age_group_id", $request->get('age_group'));
				});
			},	'locations.page', 'locations.locationAgeGroups.page'])->get()->transform(function($item){
					$item->locations->transform(function($location){
						$start_date = Carbon::now();

						$location->locationAgeGroups->transform(function($ageGroup) {
							return $ageGroup->only(['id', 'location_id', 'age_group_id', 'listing_title', 'listing_text', 'listing_image_url','page']);
						});
						$location->minimum_price = $location->trips()->where('start_date', '>=', $start_date)->min('trip_fee');
						return $location;

					});
					$item->locations;
					return $item;
				});
        if($location){
            return response()->json($location);
        }
        return response()->json(['error' => 'No Record Found'], 422);
    }
	public function getAgeGroupById(AgeGroup $ageGroup){
		$ageGroup = $ageGroup->with('page')->where('id', $ageGroup->id)->first();
        return response()->json(['status' => true, 'ageGroup' => $ageGroup]);
	}
}