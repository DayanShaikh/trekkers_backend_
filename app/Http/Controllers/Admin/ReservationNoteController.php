<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ReservationNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationNoteController extends Controller
{

	 /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->authorizeResource(ReservationNote::class, 'reservation_note');
	}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return ReservationNote::with('reservation')
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })
        ->when($request->get("status") != null, function($query) use ($request){
            $query->where('status', $request->status );    
        })
		->when($request->get('reservation_id') !== '', function ($query) use($request){
			$query->where("reservation_id", $request->get('reservation_id'));
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
            'reservation_id'     =>  ['int','required'],
            'notes'   =>  [''],
           ]);
           $data['creater_id'] = auth()->user()->id;
           $reservationNote = ReservationNote::create($data);
           return response()->json([
            'status'    =>  true,
            'reservationNote' => $reservationNote,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReservationNote  $reservationNote
     * @return \Illuminate\Http\Response
     */
    public function show(ReservationNote $reservationNote)
    {
        return response()->json(['status' => true, 'reservationNote' => $reservationNote]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReservationNote  $reservationNote
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReservationNote $reservationNote)
    {
        $data = $request->validate([
            'reservation_id'     =>  ['int','required'],
            'notes'   =>  [''],
           ]);

           $reservationNote->update($data);
           return response()->json([
            'status'    =>  true,
            'reservationNote' => $reservationNote,
        ]);
    }

     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReservationNote  $reservationNote
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, ReservationNote $reservationNote)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);

        $reservationNote->update(['status' => $request->boolean('status')]);
        $reservationNote->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReservationNote  $reservationNote
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReservationNote $reservationNote)
    {
        $reservationNote->delete();
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
            $reservationNote = ReservationNote::find($id);
            if(Auth::user()->can('delete', $reservationNote)) {
                $reservationNote->delete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to delete Reservation Note ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these Reservation Note ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $reservationNote = ReservationNote::withTrashed()->find($id);
		if(Auth::user()->can('restore', $reservationNote)) {
			$reservationNote->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore Reservation Note ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $reservationNote = ReservationNote::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $reservationNote)) {
			$reservationNote->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response(['status' => 'You do not have permission to Permanent Delete Reservation Note ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $reservationNote = ReservationNote::withTrashed()->find($id);
            if(Auth::user()->can('restore', $reservationNote)) {
                $reservationNote->restore();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Restore Reservation Note ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these Reservation Note ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $reservationNote = ReservationNote::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $reservationNote)) {
                $reservationNote->forceDelete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Permanent Delete Reservation Note ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these Reservation Note ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
