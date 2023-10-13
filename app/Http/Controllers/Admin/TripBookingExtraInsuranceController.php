<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TripBookingExtraInsurance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripBookingExtraInsuranceController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(TripBookingExtraInsurance::class, 'trip_booking_extra_insurance');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TripBookingExtraInsurance::with('tripBooking', 'tripBooking.trip', 'tripBooking.trip.location', 'tripBooking.trip.ageGroups')
        ->when('tripBooking', function ($q) use($request){
            if($request->trip_booking_id) {
                $q->where('trip_booking_id', $request->trip_booking_id );
            }
        })
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })
        ->when($request->get("status") != null, function($query) use ($request){
            $query->where('status', $request->status );    
        })
        ->filter($request->only('search'))
        ->orderBy($request->order_by ?? 'insurance', $request->order ?? 'desc')
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
            'trip_booking_id' => ['int'],
            'date' =>  ['required'],
            'insurance'   =>  ['required'],
            'survival_adventure_insurance'  =>  ['required'],
            'travel_insurance'  =>  ['required'],
            'insurance_admin_charges'  =>  ['required'],
            'is_completed'  =>  ['required'],
            'payment_date'  =>  ['required'],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $tripBookingExtraInsurance = TripBookingExtraInsurance::create($data);
       
        return response()->json([
            'status'    =>  true,
            'tripBookingExtraInsurance' => $tripBookingExtraInsurance,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripBookingExtraInsurance  $tripBookingExtraInsurance
     * @return \Illuminate\Http\Response
     */
    public function show(TripBookingExtraInsurance $tripBookingExtraInsurance)
    {
		$tripBookingExtraInsurance = $tripBookingExtraInsurance->with('tripBooking')->where('id', $tripBookingExtraInsurance->id)->first();
        return response()->json(['status' => true, 'tripBookingExtraInsurance' => $tripBookingExtraInsurance]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripBookingExtraInsurance  $tripBookingExtraInsurance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TripBookingExtraInsurance $tripBookingExtraInsurance)
    {
        $data = $request->validate([
            'trip_booking_id' => ['int'],
            'date' =>  ['required'],
            'insurance'   =>  ['required'],
            'survival_adventure_insurance'  =>  ['required'],
            'travel_insurance'  =>  ['required'],
            'insurance_admin_charges'  =>  ['required'],
            'is_completed'  =>  ['required'],
            'payment_date'  =>  ['required'],
        ]);
        $tripBookingExtraInsurance->update($data);
       
        return response()->json([
            'status'    =>  true,
            'tripBookingExtraInsurance' => $tripBookingExtraInsurance,
        ]);
    }

    /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripBookingExtraInsurance  $tripBookingExtraInsurance
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, TripBookingExtraInsurance $tripBookingExtraInsurance)
	{
        $validated = $request->validate([
			'is_completed' => ['required'],
		]);
       
        $tripBookingExtraInsurance->update(['is_completed' => $request->boolean('is_completed')]);
        $tripBookingExtraInsurance->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripBookingExtraInsurance  $tripBookingExtraInsurance
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripBookingExtraInsurance $tripBookingExtraInsurance)
    {
        $tripBookingExtraInsurance->delete();
        return response()->json([
			'status' => true,
		]);
    }

    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripBookingExtraInsurance = TripBookingExtraInsurance::find($id);
            if(Auth::user()->can('delete', $tripBookingExtraInsurance)) {
                $tripBookingExtraInsurance->delete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to delete Trip Booking Extra Insurance ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors
        ]);
    }

    public function restore($id)
    {
        $tripBookingExtraInsurance = TripBookingExtraInsurance::withTrashed()->find($id);
		if(Auth::user()->can('restore', $tripBookingExtraInsurance)) {
			$tripBookingExtraInsurance->restore();
			return response()->json(['status' => true]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore Trip Booking Extra Insurance ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $tripBookingExtraInsurance = TripBookingExtraInsurance::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $tripBookingExtraInsurance)) {
			$tripBookingExtraInsurance->forceDelete();
			return response()->json(['status' => true]);
		}
		else {
			return response(['status' => 'You do not have permission to Permanent Delete Trip Booking Extra Insurance ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripBookingExtraInsurance = TripBookingExtraInsurance::withTrashed()->find($id);
            if(Auth::user()->can('restore', $tripBookingExtraInsurance)) {
                $tripBookingExtraInsurance->restore();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Restore Trip Booking Extra Insurance ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripBookingExtraInsurance = TripBookingExtraInsurance::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $tripBookingExtraInsurance)) {
                $tripBookingExtraInsurance->forceDelete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Permanent Delete Trip Booking Extra Insurance ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors,
        ]);
	}
}