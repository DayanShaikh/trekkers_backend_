<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Utility;


class TripController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(Trip::class, 'trip');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		$date = Carbon::now();
        return Trip::with(['location', 'ageGroups' => function($qu) use($request){
			$qu->when($request->get('age_group_id'), function ($query) use($request){
				$query->where("age_group_id", $request->get('age_group_id'));
			});
		}])
        ->when($request->get('location_id'), function ($q) use($request){
            if($request->location_id) {
                $q->where('location_id', $request->location_id );
            }
        })
        ->when($request->get('trip_status'), function ($query) use($request){
			$query->where(["status" => $request->get('trip_status')]);
            if($request->get('trip_status')==0){
			    $query->where("archive", 1);
            }
            else{
                $query->where("archive", 0);
            }
		})
		->when($request->get('archive_trip'), function ($query) use($date){
			
			$query->where("archive", $request->get('archive_trip'));
		})
		->when(($request->get('trip_id') && $request->get('trip_id')!='null'), function ($query) use($request){
			    $query->where("id", $request->get('trip_id'));
            
		})
        ->when($request->get('date'), function ($query) use($request){
            if($request->get('date')!='null'){
			    $query->where("start_date", $request->get('date'));
            }
		})
        ->when($request->get('start_date'), function ($query) use($request){
			$query->where('start_date', '>=', $request->get('start_date'))
            	->where('start_date', '<=', date('Y-m-t', strtotime($request->get('start_date'))));
		})
        ->when($request->get('search_from_location'), function($q) use($request){
            $q->whereHas('location', function ($q) use($request){
                if($request->get('search_from_location')) {
                    $q->where('title', 'ilike', '%'.$request->get('search_from_location').'%');
                }
            });
        })
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })
        // ->when($request->get("status") != null, function($query) use ($request){
        //     $query->where('status', $request->status );    
        // })
		->orderBy($request->order_by ?? 'created_at', $request->order ?? 'desc')
        ->filter($request->only('search'))
        ->paginate($request->per_page ?? 25)
        ;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'location_id' =>  ['int', 'required'],
            'total_space'  =>  ['numeric'],
            'male_female_important'  =>  [''],
            'show_client_detail'  =>  [''],
            'start_date'  =>  ['required'],
            'duration'  =>  ['int'],
            'trip_fee'  =>  ['numeric','required'],
            'trip_seats_status'  =>  [''],
            'trip_letter'  =>  [''],
            'custom_trip_letter'  =>  [''],
            'is_not_bookable'  =>  [''],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $trip = Trip::create($data);
        //$trip->ageGroups()->sync($request->get('age_groups'));
        $trip->save();
        return response()->json([
            'status'    =>  true,
            'trip' => $trip,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function show(Trip $trip)
    {
        $trip = $trip->with('location', 'ageGroups')->where('id', $trip->id)->first();
        return response()->json(['status' => true, 'trip' => $trip]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Trip $trip)
    {
        $data = $request->validate([
            'location_id' =>  ['int', 'required'],
            'total_space'  =>  ['numeric'],
            'male_female_important'  =>  [''],
            'show_client_detail'  =>  [''],
            'start_date'  =>  ['required'],
            'duration'  =>  ['int'],
            'trip_fee'  =>  ['numeric','required'],
            'trip_seats_status'  =>  [''],
            'is_not_bookable'  =>  [''],
            'trip_seats_status'  =>  [''],
            'trip_letter'  =>  [''],
            'custom_trip_letter'  =>  [''],
        ]);
        //$trip->ageGroups()->sync($request->get('age_groups'));
        $trip->update($data);
        $trip->save();
        return response()->json([
            'status'    =>  true,
            'trip' => $trip,
        ]);
    }

    /**
     * Update the Lock Status for trip.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, Trip $trip)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        $trip->update(['status' => $request->boolean('status')]);
        $trip->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function destroy(Trip $trip)
    {
        $trip->delete();
        return response()->json([
			'status' => true,
            'message'   =>  'Record has been deleted'
		]);
    }

    public function massDestroy(Request $request)
	{

        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $trip = Trip::find($id);
            if(Auth::user()->can('delete', $trip)) {
                $trip->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these trips ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $trip = Trip::withTrashed()->find($id);
		if(Auth::user()->can('restore', $trip)) {
			$trip->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored']);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore Trip ID: '.$id,
            ], 403);
		}
    }
    public function forceDelete($id)
    {
        $trip = Trip::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $trip)) {
			$trip->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted']);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to Permanent Delete Trip ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $trip = Trip::withTrashed()->find($id);
            if(Auth::user()->can('restore', $trip)) {
                $trip->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these roles ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $trip = Trip::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $trip)) {
                $trip->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these roles ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}

	/**
     * Display the specified resource.
     *
     * @param  \App\Models\Trip  $trip
     * @return \Illuminate\Http\Response
     */
    public function tripDates(Trip $trip)
    {
        $trip = Trip::orderBy('start_date', 'asc')->first(['id', 'start_date']);
		if($trip->start_date){
			$last_date = $trip->start_date;
		}
		else{
			$last_date = Carbon::now()->toDateString();
		}
		$trip1 = Trip::orderBy('start_date', 'desc')->first(['id', 'start_date']);
		if($trip1->start_date){
			$end_date = $trip1->start_date;
		}
		else{
			$end_date = Carbon::now()->toDateString();
		}
		$total_months=date("n", strtotime($end_date))+(12-date("n", strtotime($last_date)))+12*(date("Y", strtotime($end_date))-date("Y", strtotime($last_date))-1);
		$dates = [];
		for($i=date("n", strtotime($last_date)); $i<=$total_months+date("n", strtotime($last_date)); $i++){
			$dates[] = date("Y-m-01", strtotime($i." month"));
			//$date_val=date("F Y", strtotime($date_str));
		}
        return response()->json(['status' => true, 'date_str' => $dates]);
    }

    public function partnerEmail(Location $location)
    {
        $data = User::whereHas('locations', function ($query) use($location) {
            $query->where('id', $location->id);
        })->whereHas('userFields', function ($query) {
            $query->where('field_key', 'is_partner')
            ->where('field_value', 1);
        })->get();

        foreach($data as $user) {
            $subject = Utility::getConfig('partner_email_subject')->value;
				$user_email_body = Utility::getConfig('partner_email_body')->value;
				$user_email_body=str_replace(
					array(
						'[$name]',
						
					),
					array(
						$user->name,
					),
					$user_email_body
				);
				Utility::send_email($user->email, $subject, $user_email_body, 0);
        }

        return $data;
    }
    
}
