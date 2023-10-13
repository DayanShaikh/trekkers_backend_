<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\Trip;
use App\Models\TripType;
use App\Models\Reminder;
use App\Models\LandingPage;
use DB;
use Carbon\Carbon;
use App\Utility;


class TripController extends Controller
{

        public function trips(Request $request)
        {
			$page = LandingPage::where("type", 5)->first();
			if($page){
				$page = $page->page;			
			}
			$date = Carbon::now();
			$dates = explode( "-", $request->get('dates') );
			$start_date = '';
			$end_date = '';
			$trip_types = [];
			if( count( $dates ) == 2 ) {
				$start_date = explode( "/", trim($dates[ 0 ]) );
				if( count( $start_date ) == 3 ) {
					$start_date = "20".$start_date[2]."-".$start_date[1]."-".$start_date[0];
				}
				$end_date = explode( "/", trim($dates[ 1 ]) );
				if( count( $end_date ) == 3 ) {
					$end_date = "20".$end_date[2]."-".$end_date[1]."-".$end_date[0];
				}
				
			}
			$types = [];
			if($request->get('trip_types')){
				foreach($request->get('trip_types') as $key => $type){
					if($type=='true'){
						$types[] = $key;
					}
					//return $trip_types;
				}
				//return $types;
				$trip_types =  $types;
				//return $trip_types;
			}
		// 	$trip = Location::with(['page', 'page.pageGalleries' => function($queryg){
		// 		$queryg->where('status', true)->inRandomOrder()->limit(5);
		// 	}, 'trips' => function($query) use($request, $start_date, $end_date, $dates, $date){
		// 		$query->when($request->has('dates'), function($query) use($start_date, $end_date, $dates){
		// 			if( count( $dates ) == 2 ) {
		// 				$query->whereBetween('start_date', [$start_date, $end_date]);
		// 			}
		// 		});
		// 		$query->where("start_date", ">=", $date->format("Y-m-d"));
		// 		$query->where('status', true);
		// 		$query->orderBy('start_date');
		// 		//$query->limit(1);
				
		// 		// $query->where('start_date', '=', DB::raw('start_date', 'min(start_date)'));
		// 		// $query->select('id', 'location_id', 'start_date')->first();
				
			
		// }])
		// 	->whereHas('trips', function($q) use($request, $start_date, $end_date, $dates, $date){
		// 		$q->when($request->has('dates'), function($query) use($start_date, $end_date, $dates){
		// 			if( count( $dates ) == 2 ) {
		// 				$query->whereBetween('start_date', [$start_date, $end_date]);
		// 			}
		// 		})
		// 		->where("start_date", ">=", $date->format("Y-m-d"))
		// 		->where('status', true)
		// 		->orderBy('start_date');
				
		// 	})
		// 	->whereHas('attributes', function($q) use($request){
		// 		if($request->get('europe_only')=="true"){
		// 			$q->where('attribute_id', 16);
		// 		}
		// 		if($request->get('all_other')=="true"){
		// 			$q->where('attribute_id', '!=', 16);
		// 		}
		// 	})
		// 	->whereHas('tripTypes', function($q) use($trip_types){
		// 		if(!empty($trip_types)){
		// 			$q->whereIn('trip_type_id', $trip_types);
		// 		}
				
		// 	})
		// 	->where('status', true)
		// 	->paginate($request->get("limit", $request->per_page ?? 12));
            $trip = Trip::whereHas('location', function($q) use($request, $trip_types){
				$q->whereHas('attributes', function($q) use($request){
					if($request->get('europe_only')=="true"){
						$q->where('attribute_id', 16);
					}
					if($request->get('all_other')=="true"){
						$q->where('attribute_id', '!=', 16);
					}
				});
				$q->whereHas('tripTypes', function($q) use($trip_types){
					if(!empty($trip_types)){
						$q->whereIn('trip_type_id', $trip_types);
					}
					
				});
				$q->where('status', true);
			})->with(['location', 'location.page', 'location.page.pageGalleries' => function($qu) use($request){
				$qu->where('status', true)->limit(5);
			}])
			
            ->orderBy('start_date')
			->where("start_date", ">=", $date->format("Y-m-d"))
			->where('status', true)
            ->when($request->has('dates'), function($query) use($start_date, $end_date, $dates){
				if( count( $dates ) == 2 ) {
                	$query->whereBetween('start_date', [$start_date, $end_date]);
				}
            })
			
            ->paginate($request->get("limit", $request->per_page ?? 12));
			if($trip){
				return response()->json(["trip" => $trip, "page" => $page]);
			}
			return response()->json(['error' => 'No Record Found'], 422);
		}
		public function tripSlider(Request $request)
        {
			$ids = explode(',', $request->ids);
			$trips = [];
			foreach($ids as $id){
				$location = Location::find($id);
				if(!$location){
					$location = Location::where("status", true)->inRandomOrder()->first();
				}
				$trip = $location->trips()->select(['id','start_date', 'trip_fee', 'duration'])->orderBy("start_date")->whereDate("start_date", ">", Carbon::now())->first();
				if($trip){
					$trip["location"] = $location;
					$trip["location"]["page"]["page_name"] = $location->page->page_name;
					$trips[] = $trip;
				}
			}
			return response()->json(["trip" => $trips]);
		}
		public function upcomingTrips(Request $request)
        {
			$trips = Trip::with('location')->where('start_date', '>=', date("Y-m-d"))->where('start_date', '<=', date("Y-m-d", strtotime( "+6 weeks" )))->where('status', true)->orderBy('start_date')->get();
			$upcoming_trips = '';
			if($trips){
				$upcoming_trips = '<div class="booking_list mob_view">
			<h3>Lastminute reizen</h3>
			<table cellpadding="0" cellspacing="0" class="table table-striped">';
			$month = "";
			foreach( $trips as $trip ){
				//return $trip;
				if($trip['is_not_bookable'] == 1 || $trip["space"]->remaining == 0) continue;
				$trip_month = date( "n", strtotime( $trip[ "start_date" ] ));
				if( $month != $trip_month ) {
					$month = $trip_month;
					$upcoming_trips .= '<tr>
						<th colspan="5">'.Utility::$months[ $month-1 ].'</th>
					</tr>';
				}
				$upcoming_trips .= '<tr>
					<td>'.date( "d/m", strtotime( $trip[ "start_date" ] )).'</td>
					<td class="text-center">'.($trip["trip_seats_status"]==1?'<div class="status_orange"></div>':($trip["trip_seats_status"]==2?'<div class="status_red"></div>':'')).'</td>
					<td><a href="'.config('app.url')."/".$trip->location->page->page_name.'.html">'.$trip->location->title.'</a></td>
					<td width="15%">'. $trip[ "trip_fee" ] .'</td>
				</tr>';
			}
			$upcoming_trips .= '</table>
			</div>';
				return response()->json($upcoming_trips);
			}
			return response()->json(['error' => 'No Record Found'], 422);
		}
		public function tripTypes(Request $request)
        {
			$tripType = TripType::where('status', true)->orderBy('sortorder')->get();
			if($tripType){
				return response()->json($tripType);
			}
			return response()->json(['error' => 'No Record Found'], 422);
		}
		public function upcomingTripsSpot(Request $request)
        {
			$trips = Trip::with('location', 'location.page')->where('start_date', '>=', date("Y-m-d"))->where('start_date', '<=', date("Y-m-d", strtotime( "+12 weeks" )))->where('status', true)->orderBy('start_date')->get();
			//return $trips;
			$upcoming_trips = '';
			if($trips){
				$locations = [];
				$spaces_used = [];
				$upcoming_trips = '<div class="booking_list booking_list_spot mob_view">
				<h3>Laatste kans</h3>
				<table cellpadding="0" cellspacing="0" class="table table-striped">';
				$month = "";
				$prev_loc = "";
				foreach( $trips as $trip ){
					//return $trip;
					if(!isset($locations[$trip["location_id"]])){
						$locations[$trip["location_id"]] = Location::where('id', $trip["location_id"])->first();
					}
					$location = $locations[$trip["location_id"]];
					if($prev_loc && $prev_loc == $trip["location_id"]){
						continue;
					}
					else{
						$prev_loc = $trip["location_id"];
					}
					//return $trip["space"]->remaining;
					if($trip['is_not_bookable'] == 1 || $trip["space"]->remaining > 3 || $trip["space"]->remaining == 0) continue;
					//if($trip["is_not_bookable"] == 1 || $trip["space"]->remaining > 3 || $trip["space"]->remaining == 0){
						//return $trip["space"]->remaining;
						$trip_month = date( "n", strtotime( $trip[ "start_date" ] ));
						if( $month != $trip_month ) {
							$month = $trip_month;
							
							$upcoming_trips .= '<tr>
								<th width="10%">'.Utility::$months[ $month-1 ].'</th>
								<th class="mob_none">Groepsreis</th>
								<th class="mob_none" width="12%">Reissom</th>
								<th class="mob_none" width="15%">Plekken beschikbaar</th>
								<th class="mob_none" width="18%">Laatste plek</th>
							</tr>';
					
						}
						$female = '';
						$male = '';
						if($trip["space"]->female_remaining==1 && $trip["space"]->remaining==1){
							$female = 'voor een vrouw ';
						}
						elseif($trip["space"]->male_remaining==1 && $trip["space"]->remaining==1){
							$male = 'voor een man ';
						}
						$upcoming_trips .= '<tr>
							<td>'.date( "d/m", strtotime( $trip[ "start_date" ] )).'</td>
							<td><a href="'.config('app.url')."/".$trip->location->page->page_name.'.html">'. $trip->location->title.'</a></td>
							<td>'.$trip[ "trip_fee" ].'</td>
							<td>'.$trip["space"]->remaining.'</td>
							<td>'.$male.' <br> '.$female.'</td>
						</tr>';
					
				}
			$upcoming_trips .= '</table>
			</div>';
				//return $upcoming_trips;
				return response()->json($upcoming_trips);
			}
			return response()->json(['error' => 'No Record Found'], 422);
		}
		public function saveReminder(Request $request){
			
			if($request->trip_id) {
				$trip = Trip::where('id', $request->trip_id)->first();
				if($trip){
					$saveDetails = Reminder::create([
						"trip_id" => $trip->id,
						"email" => $request->email,
					]);
					return response()->json(['status' => 1, 'message' => 'Bedankt! We geven je een seintje als het bijna tijd is om je droomvakantie vast te leggen.'], 200);
				}
			}
			else{
				return response()->json([
					'status' => false
				]);
			}
		}
}
