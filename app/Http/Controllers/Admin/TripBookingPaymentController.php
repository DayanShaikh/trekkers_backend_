<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TripBookingPayment;
use App\Models\TripBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripBookingPaymentController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(TripBookingPayment::class, 'trip_booking_payment');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TripBookingPayment::with('tripBooking')
        ->when('booking_id', function ($q) use($request){
         if($request->booking_id) {
             $q->where('trip_booking_id', $request->booking_id );
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
		->when($request->get('trip_booking_id'), function ($query) use($request){
			$query->where("trip_booking_id", $request->get('trip_booking_id'));
		})
         ->filter($request->only('search'))
         ->orderBy($request->order_by ?? 'notes', $request->order ?? 'desc')
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
            'trip_booking_id'     =>  ['int'],
            'payment_type'   =>  ['required'],
            'payment_date'   =>  [''],
            'amount'   =>  [''],
            'transaction_reference'   =>  [''],
            'details'   =>  [''],
           ]);
           $data['creater_id'] = auth()->user()->id;
           $tripBookingPayment = TripBookingPayment::create($data);
		   $booking = TripBooking::find($tripBookingPayment->trip_booking_id);
		   $booking->update(["paid_amount" => $tripBookingPayment->amount]);
           return response()->json([
            'status'    =>  true,
            'tripBookingPayment' => $tripBookingPayment,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripBookingPayment  $tripBookingPayment
     * @return \Illuminate\Http\Response
     */
    public function show(TripBookingPayment $tripBookingPayment)
    {
        return response()->json(['status' => true, 'tripBookingPayment' => $tripBookingPayment]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripBookingPayment  $tripBookingPayment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TripBookingPayment $tripBookingPayment)
    {
        $data = $request->validate([
            'trip_booking_id'     =>  ['int'],
            'payment_type'   =>  ['required'],
            'payment_date'   =>  [''],
            'amount'   =>  [''],
            'transaction_reference'   =>  [''],
            'details'   =>  [''],
           ]);

           $tripBookingPayment->update($data);
		   $booking = TripBooking::find($tripBookingPayment->trip_booking_id);
		   $booking->update(["paid_amount" => $tripBookingPayment->amount]);
           return response()->json([
            'status'    =>  true,
            'tripBookingPayment' => $tripBookingPayment,
        ]);
    }


     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripBookingPayment  $tripBookingPayment
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, TripBookingPayment $tripBookingPayment)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        $tripBookingPayment->update(['status' => $request->boolean('status')]);
        $tripBookingPayment->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripBookingPayment  $tripBookingPayment
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripBookingPayment $tripBookingPayment)
    {
        $tripBookingPayment->delete();
        return response()->json([
            'status'    =>  true,
            'message'   =>  'Record has been deleted'
        ]);
    }

    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripBookingPayment = TripBookingPayment::find($id);
            if(Auth::user()->can('delete', $tripBookingPayment)) {
                $tripBookingPayment->delete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to delete Trip Booking Payment ID: ".$id.""];
            }
        }
		return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these Trip Booking Payment ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $tripBookingPayment = TripBookingPayment::withTrashed()->find($id);
		if(Auth::user()->can('restore', $tripBookingPayment)) {
			$tripBookingPayment->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore Trip Booking Payment ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $tripBookingPayment = TripBookingPayment::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $tripBookingPayment)) {
			$tripBookingPayment->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response(['status' => 'You do not have permission to Permanent Delete Trip Booking Payment ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripBookingPayment = TripBookingPayment::withTrashed()->find($id);
            if(Auth::user()->can('restore', $tripBookingPayment)) {
                $tripBookingPayment->restore();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Restore Trip Booking Payment ID: ".$id.""];
            }
        }
		return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these Trip Booking Payment ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripBookingPayment = TripBookingPayment::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $tripBookingPayment)) {
                $tripBookingPayment->forceDelete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Permanent Delete Trip Booking Payment ID: ".$id.""];
            }
        }
		return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these Trip Booking Payment ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
