<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TripBookingNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripBookingNoteController extends Controller
{
     /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(TripBookingNote::class, 'trip_booking_note');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TripBookingNote::with('tripBooking')
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
            'trip_booking_id'     =>  ['int','required'],
            'notes'   =>  [''],
			'is_log' => [''],
			'is_publish' => ['']
		]);
		$data['creater_id'] = auth()->user()->id;
		$data['is_log'] = true;
		$tripBookingNote = TripBookingNote::create($data);
		return response()->json([
            'status'    =>  true,
            'tripBookingNote' => $tripBookingNote,
		]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripBookingNote  $tripBookingNote
     * @return \Illuminate\Http\Response
     */
    public function show(TripBookingNote $tripBookingNote)
    {
        return response()->json(['status' => true, 'tripBookingNote' => $tripBookingNote]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripBookingNote  $tripBookingNote
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TripBookingNote $tripBookingNote)
    {
        $data = $request->validate([
            'trip_booking_id'     =>  ['int','required'],
            'notes'   =>  [''],
			'is_publish' => ['']
           ]);

           $tripBookingNote->update($data);
           return response()->json([
            'status'    =>  true,
            'tripBookingNote' => $tripBookingNote,
        ]);
    }

     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripBookingNote  $tripBookingNote
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, TripBookingNote $tripBookingNote)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);

        $tripBookingNote->update(['status' => $request->boolean('status')]);
        $tripBookingNote->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripBookingNote  $tripBookingNote
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripBookingNote $tripBookingNote)
    {
        $tripBookingNote->delete();
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
            $tripBookingNote = TripBookingNote::find($id);
            if(Auth::user()->can('delete', $tripBookingNote)) {
                $tripBookingNote->delete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to delete Trip Booking Note ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these Trip Booking Note ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $tripBookingNote = TripBookingNote::withTrashed()->find($id);
		if(Auth::user()->can('restore', $tripBookingNote)) {
			$tripBookingNote->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore Trip Booking Note ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $tripBookingNote = TripBookingNote::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $tripBookingNote)) {
			$tripBookingNote->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response(['status' => 'You do not have permission to Permanent Delete Trip Booking Note ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripBookingNote = TripBookingNote::withTrashed()->find($id);
            if(Auth::user()->can('restore', $tripBookingNote)) {
                $tripBookingNote->restore();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Restore Trip Booking Note ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these Trip Booking Note ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripBookingNote = TripBookingNote::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $tripBookingNote)) {
                $tripBookingNote->forceDelete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Permanent Delete Trip Booking Note ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these Trip Booking Note ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
