<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(Reservation::class, 'reservation');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		return Reservation::query()
		->with('user','trip', 'trip.location')
        ->whereHas('trip', function($query) use($request){
			$query->when($request->get('archive_trip'), function ($query) use($request){
				$query->where("archive", $request->get('archive_trip'));
			});
		})
		->when($request->get("trash"), function($query) use ($request){
			if($request->get('trash')==1){
				$query->onlyTrashed();
			}
		})
        ->when($request->get("status") != null, function($query) use ($request){
            $query->where('status', $request->status );    
        })
        ->when($request->get('user_id'), function ($q) use($request){
            if($request->user_id) {
                $q->where('user_id', $request->user_id );
            }
        })
        ->when($request->get('trip_id'), function ($q) use($request){
            if($request->trip_id) {
                $q->where('trip_id', $request->trip_id );
            }
        })
        ->when($request->get('date_added'), function ($query) use($request){
			$query->where("created_at", $request->get('date_added'));
		})
        ->when($request->get('email_sent'), function ($query) use($request){
			$query->where("email_sent", $request->get('email_sent'));
		})
        ->filter($request->only('search'))
        ->orderBy($request->order_by ?? 'created_at', $request->order ?? 'desc')
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
            'trip_id' => ['int', 'required'],
            'trip_booking_id' =>  ['int', 'required'],
            'user_id'   =>  [''],
            'child_firstname'   =>  [''],
            'child_lastname' =>  [''],
            'gender'   =>  ['required'],
            'child_dob'   =>  [''],
            'parent_name' =>  [''],
            'parent_email'   =>  [''],
            'email'   =>  ['string', 'required'],
            'address' =>  [''],
            'house_number'   =>  [''],
            'city'   =>  [''],
            'postcode' =>  [''],
            'telephone'   =>  [''],
            'cellphone'   =>  [''],
            'whatsapp_number' =>  [''],
            'location_pickup_id'   =>  [''],
            'child_diet'   =>  [''],
            'child_medication' =>  [''],
            'about_child'   =>  [''],
            'date_added'   =>  [''],
            'can_drive' =>  [''],
            'have_driving_license'   =>  [''],
            'have_creditcard'   =>  [''],
            'trip_fee'   =>  [''],
            'total_amount'   =>  [''],
            'paid_amount'   =>  [''],
            'deleted'   =>  [''],
            'payment_reminder_email_sent'   =>  [''],
            'email_sent'   =>  [''],
            'login_reminder_email_sent'   =>  [''],
            'upsell_email_sent'   =>  [''],
            'deposit_reminder_email_sent'   =>  [''],
            'display_name'   =>  [''],
            'additional_address'   =>  [''],
            'contact_person_name'   =>  [''],
            'contact_person_extra_name'   =>  [''],
            'contact_person_extra_cellphone'   =>  [''],
            'reservation_fees'   =>  [''],
            'reservation_fees_paid_at'   =>  [''],
            'reservation_fees_payment_type'   =>  [''],
            'expiry_date'   =>  [''],

        ]);
        $data['creater_id'] = auth()->user()->id;
        $reservation = Reservation::create($data);
        return response()->json([
            'status'    =>  true,
            'reservation' => $reservation,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function show(Reservation $reservation)
    {
        return response()->json(['status' => true, 'reservation' => $reservation]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reservation $reservation)
    {
        $data = $request->validate([
            'trip_id' => ['int'],
            'trip_booking_id' =>  [''],
            'user_id'   =>  [''],
            'child_firstname'   =>  [''],
            'child_lastname' =>  [''],
            'gender'   =>  [''],
            'child_dob'   =>  [''],
            'parent_name' =>  [''],
            'parent_email'   =>  [''],
            'email'   =>  [''],
            'address' =>  [''],
            'house_number'   =>  [''],
            'city'   =>  [''],
            'postcode' =>  [''],
            'telephone'   =>  [''],
            'cellphone'   =>  [''],
            'whatsapp_number' =>  [''],
            'location_pickup_id'   =>  [''],
            'child_diet'   =>  [''],
            'child_medication' =>  [''],
            'about_child'   =>  [''],
            'date_added'   =>  [''],
            'can_drive' =>  [''],
            'have_driving_license'   =>  [''],
            'have_creditcard'   =>  [''],
            'trip_fee'   =>  [''],
            'total_amount'   =>  [''],
            'paid_amount'   =>  [''],
            'deleted'   =>  [''],
            'payment_reminder_email_sent'   =>  [''],
            'email_sent'   =>  [''],
            'login_reminder_email_sent'   =>  [''],
            'upsell_email_sent'   =>  [''],
            'deposit_reminder_email_sent'   =>  [''],
            'display_name'   =>  [''],
            'additional_address'   =>  [''],
            'contact_person_name'   =>  [''],
            'contact_person_extra_name'   =>  [''],
            'contact_person_extra_cellphone'   =>  [''],
            'reservation_fees'   =>  [''],
            'reservation_fees_paid_at'   =>  [''],
            'reservation_fees_payment_type'   =>  [''],
            'expiry_date'   =>  [''],

        ]);
        $reservation->update($data);
        return response()->json([
            'status'    =>  true,
            'reservation' => $reservation,
        ]);
    }

     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, Reservation $reservation)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        $reservation->update(['status' => $request->boolean('status')]);
        $reservation->save();
		return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return response()->json([
			'status' => true,
            'message'   =>  'Decord has been deleted',
		]);
    }

    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $reservation = Reservation::find($id);
            if(Auth::user()->can('delete', $reservation)) {
                $reservation->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these reservations ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $reservation = Reservation::withTrashed()->find($id);
		if(Auth::user()->can('restore', $reservation)) {
			$reservation->restore();
			return response()->json([
                'status' => true,
                'message'   =>  'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore reservation ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $reservation = Reservation::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $reservation)) {
			$reservation->forceDelete();
			return response()->json([
                'status' => true,
                'message'   =>  'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete reservation ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $reservation = Reservation::withTrashed()->find($id);
            if(Auth::user()->can('restore', $reservation)) {
                $reservation->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these reservations ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $reservation = Reservation::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $reservation)) {
                $reservation->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these reservations ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
