<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TripTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripTicketController extends Controller
{
    
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(TripTicket::class, 'trip_ticket');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TripTicket::with('trip','airline')
        ->when($request->trip_id, function ($q) use($request){
            if($request->trip_id) {
                $q->where('trip_id', $request->trip_id );
            }
         }) 
         ->when('airline', function ($q) use($request){
            if($request->airline_id) {
                $q->where('airline_id', $request->airline_id );
            }
        })
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })
         ->filter($request->only('search'))
         ->orderBy($request->order_by ?? 'type', $request->order ?? 'desc')
         ->paginate($request->per_page ?? 10);
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
            'trip_id' =>  [''], 
            'airline_id'   =>  [''],
            'connecting_flight'   =>  [''], 
            'type'   =>  [''],
            'datum'   =>  [''],
            'vluchtnummer'   =>  [''],
            'van'   =>  [''],
            'naar'   =>  [''],
            'vertrek'   =>  [''],
            'ankomst'   =>  [''],
            'sortorder'   =>  [''],

        ]);
        $data['creater_id'] = auth()->user()->id;
        $tripTicket = TripTicket::create($data);
        return response()->json([
            'status'    =>  true,
            'tripTicket' => $tripTicket,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripTicket  $tripTicket
     * @return \Illuminate\Http\Response
     */
    public function show(TripTicket $tripTicket)
    {
        $tripTicket = $tripTicket->with('trip','airline','connectingFlight')->where('id', $tripTicket->id)->first();
        return response()->json(['status' => true, 'tripTicket' => $tripTicket]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripTicket  $tripTicket
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TripTicket $tripTicket)
    {
        $data = $request->validate([
            'trip_id' =>  [''], 
            'airline_id'   =>  [''],
            'connecting_flight'   =>  [''], 
            'type'   =>  [''],
            'datum'   =>  [''],
            'vluchtnummer'   =>  [''],
            'van'   =>  [''],
            'naar'   =>  [''],
            'vertrek'   =>  [''],
            'ankomst'   =>  [''],
            'sortorder'   =>  [''],

        ]);
        $tripTicket->update($data);
        return response()->json([
            'status'    =>  true,
            'tripTicket' => $tripTicket,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripTicket  $tripTicket
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripTicket $tripTicket)
    {
        $tripTicket->delete();
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
            $tripTicket = TripTicket::find($id);
            if(Auth::user()->can('delete', $tripTicket)) {
                $tripTicket->delete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to delete Trip Ticket ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these Trip Ticket ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $tripTicket = TripTicket::withTrashed()->find($id);
		if(Auth::user()->can('restore', $tripTicket)) {
			$tripTicket->restore();
			return response()->json([
                'status' => true,
                'message'   =>  'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore Trip Ticket ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $errors = [];
            $tripTicket = TripTicket::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $tripTicket)) {
                $tripTicket->forceDelete();
                return response()->json([
                    'status' => true,
                    'message'   =>  'Record has been permanent deleted'
                ]);
            }
            else{
                return response(['status' => 'You do not have permission to Permanent Delete Trip Ticket ID: '.$id], 403);
            }
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripTicket = TripTicket::withTrashed()->find($id);
            if(Auth::user()->can('restore', $tripTicket)) {
                $tripTicket->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these Trip Ticket ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripTicket = TripTicket::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $tripTicket)) {
                $tripTicket->forceDelete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Permanent Delete Trip Ticket ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these Trip Ticket ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
