<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TripTicketUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripTicketUserController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(TripTicketUser::class, 'trip_ticket_user');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TripTicketUser::with('tripBooking')
        ->when($request->trip_ticket_id, function ($q) use($request){
                if($request->trip_ticket_id) {
                    $q->where('trip_ticket_id', $request->trip_ticket_id );
                }
            })
        ->when($request->trip_booking_id, function ($q) use($request){
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
			'trip_ticket_id' 	=>  ['int'],
            'trip_booking_id' 	=>  ['int'],
            'ticket_number'  	=>  [''],
			'notes'  			=>  [''],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $tripTicketUser = TripTicketUser::create($data);
        return response()->json([
            'status'    =>  true,
            'tripTicketUser' => $tripTicketUser,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripTicketUser  $tripTicketUser
     * @return \Illuminate\Http\Response
     */
    public function show(TripTicketUser $tripTicketUser)
    {
		$tripTicketUser = $tripTicketUser->with('tripBooking')->where('id', $tripTicketUser->id)->first();
        return response()->json(['status' => true, 'tripTicketUser' => $tripTicketUser]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripTicketUser  $tripTicketUser
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TripTicketUser $tripTicketUser)
    {
        $data = $request->validate([
			'trip_ticket_id' 	=>  ['int'],
            'trip_booking_id' 	=>  ['int'],
            'ticket_number'  	=>  [''],
			'notes'  			=>  [''],
        ]);
        $tripTicketUser->update($data);
        return response()->json([
            'status'    =>  true,
            'tripTicketUser' => $tripTicketUser,
        ]);
    }

 /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripTicketUser  $tripTicketUser
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, TripTicketUser $tripTicketUser)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        $tripTicketUser->update(['status' => $request->boolean('status')]);
        $tripTicketUser->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripTicketUser  $tripTicketUser
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripTicketUser $tripTicketUser)
    {
        $tripTicketUser->delete();
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
            $tripTicketUser = TripTicketUser::find($id);
            if(Auth::user()->can('delete', $tripTicketUser)) {
                $tripTicketUser->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
			"status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these Trip Ticket User ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $tripTicketUser = TripTicketUser::withTrashed()->find($id);
		if(Auth::user()->can('restore', $tripTicketUser)) {
			$tripTicketUser->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore Trip Ticket User ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $tripTicketUser = TripTicketUser::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $tripTicketUser)) {
			$tripTicketUser->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response(['status' => 'You do not have permission to Permanent Delete Trip Ticket User ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripTicketUser = TripTicketUser::withTrashed()->find($id);
            if(Auth::user()->can('restore', $tripTicketUser)) {
                $tripTicketUser->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these Trip Ticket User ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripTicketUser = TripTicketUser::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $tripTicketUser)) {
                $tripTicketUser->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
		return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these Trip Ticket User ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
