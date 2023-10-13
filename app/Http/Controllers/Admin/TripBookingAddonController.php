<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TripBookingAddon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripBookingAddonController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(TripBookingAddon::class, 'trip_booking_addon');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        return TripBookingAddon::with('tripBooking','tripBooking.trip','tripBooking.trip.location','locationAddon')
        ->when('booking_id', function ($q) use($request){
            if($request->booking_id) {
                $q->where('trip_booking_id', $request->booking_id );
            }
        })
        ->when('location_addon_id', function ($q) use($request){
            if($request->location_addon_id) {
                $q->where('location_addon_id', $request->location_addon_id );
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
        ->orderBy($request->order_by ?? 'processed', $request->order ?? 'desc')
        ->paginate($request->per_page ?? 10)
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
            'location_addon_id' =>  ['int','required'],
            'booking_date'   =>  [''],
            'amount'  =>  [''],
            'amount_paid'  =>  [''],
            'payment_date'  =>  [''],
            'processed'  =>  [''],
            'notes'  =>  [''],
            'extra_field_1'  =>  [''],
            'extra_field_2'  =>  [''],
            'extra_field_3'  =>  [''],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $tripBookingAddon = TripBookingAddon::create($data);

        return response()->json([
            'status'    =>  true,
            'tripBookingAddon' => $tripBookingAddon,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripBookingAddon  $tripBookingAddon
     * @return \Illuminate\Http\Response
     */
    public function show(TripBookingAddon $tripBookingAddon)
    {
		$tripBookingAddon = $tripBookingAddon->with('locationAddon')->where('id', $tripBookingAddon->id)->first();
        return response()->json(['status' => true, 'tripBookingAddon' => $tripBookingAddon]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripBookingAddon  $tripBookingAddon
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TripBookingAddon $tripBookingAddon)
    {
        $data = $request->validate([
            'trip_booking_id' => ['int'],
            'location_addon_id' =>  ['int','required'],
            'booking_date'   =>  [''],
            'amount'  =>  [''],
            'amount_paid'  =>  [''],
            'payment_date'  =>  [''],
            'processed'  =>  [''],
            'notes'  =>  [''],
            'extra_field_1'  =>  [''],
            'extra_field_2'  =>  [''],
            'extra_field_3'  =>  [''],
        ]);
        $tripBookingAddon->update($data);
        return response()->json([
            'status'    =>  true,
            'tripBookingAddon' => $tripBookingAddon,
        ]);
    }

		/**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripBookingAddon  $tripBookingAddon
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, TripBookingAddon $tripBookingAddon)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);

        $tripBookingAddon->update(['status' => $request->boolean('status')]);
        $tripBookingAddon->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripBookingAddon  $tripBookingAddon
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripBookingAddon $tripBookingAddon)
    {
        $tripBookingAddon->delete();
		return response()->json([
            'status' => true,
            "message" => 'Record has been deleted'
        ]);
    }

    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripBookingAddon = TripBookingAddon::find($id);
            if(Auth::user()->can('delete', $tripBookingAddon)) {
                $tripBookingAddon->delete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to delete Trip Booking Addon ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these Trip Booking Addon ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $tripBookingAddon = TripBookingAddon::withTrashed()->find($id);
		if(Auth::user()->can('restore', $tripBookingAddon)) {
			$tripBookingAddon->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore Trip Booking Addon ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $tripBookingAddon = TripBookingAddon::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $tripBookingAddon)) {
			$tripBookingAddon->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response(['status' => 'You do not have permission to Permanent Delete Trip Booking Addon ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
    {
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripBookingAddon = TripBookingAddon::withTrashed()->find($id);
            if(Auth::user()->can('restore', $tripBookingAddon)) {
                $tripBookingAddon->restore();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Restore Trip Booking Addon ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these Trip Booking Addon ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
    }

    public function massForceDelete(Request $request)
    {
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripBookingAddon = TripBookingAddon::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $tripBookingAddon)) {
                $tripBookingAddon->forceDelete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Permanent Delete Trip Booking Addon ID: ".$id.""];
            }
        }
		return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these Trip Booking Addon ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
    }
}
