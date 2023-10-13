<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TripBookingDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TripBookingDocumentController extends Controller
{
     /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(TripBookingDocument::class, 'trip_booking_document');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TripBookingDocument::with('tripBooking')
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
         ->orderBy($request->order_by ?? 'title', $request->order ?? 'desc')
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
            'title'   =>  ['required'],
            'sortorder'   =>  [''],
           ]);
        $data['creater_id']  = auth()->user()->id;
		$tripBookingDocument = TripBookingDocument::create($data);
        if ($request->hasFile('document_url')){
			if(!empty($tripBookingDocument->document_url)){
				Storage::delete($tripBookingDocument->document_url);
			}
			$tripBookingDocument->document_url = Storage::putFile('public/trip_booking_documents', $request->file('document_url'));
		}
        $tripBookingDocument->save();
           return response()->json([
            'status'    =>  true,
            'tripBookingDocument' => $tripBookingDocument,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripBookingDocument  $tripBookingDocument
     * @return \Illuminate\Http\Response
     */
    public function show(TripBookingDocument $tripBookingDocument)
    {
        return response()->json(['status' => true, 'tripBookingDocument' => $tripBookingDocument]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripBookingDocument  $tripBookingDocument
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TripBookingDocument $tripBookingDocument)
    {
        $data = $request->validate([
            'trip_booking_id'     =>  ['int'],
            'title'   =>  ['required'],
            'sortorder'   =>  [''],
        ]);
		$tripBookingDocument->update($data);
        if ($request->hasFile('document_url')){
			if(!empty($tripBookingDocument->document_url)){
				Storage::delete($tripBookingDocument->document_url);
			}
			$tripBookingDocument->document_url = Storage::putFile('public/trip_booking_documents', $request->file('document_url'));
		}
        $tripBookingDocument->save();
        return response()->json([
            'status'    =>  true,
            'tripBookingDocument' => $tripBookingDocument,
        ]);
    }

	/**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripBookingDocument  $tripBookingDocument
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, TripBookingDocument $tripBookingDocument)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);

        $tripBookingDocument->update(['status' => $request->boolean('status')]);
        $tripBookingDocument->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripBookingDocument  $tripBookingDocument
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripBookingDocument $tripBookingDocument)
    {
        $tripBookingDocument->delete();
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
            $tripBookingDocument = TripBookingDocument::find($id);
            if(Auth::user()->can('delete', $tripBookingDocument)) {
                $tripBookingDocument->delete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to delete Trip Booking Document ID: ".$id.""];
            }
        }
		return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these Trip Booking Document ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $tripBookingDocument = TripBookingDocument::withTrashed()->find($id);
		if(Auth::user()->can('restore', $tripBookingDocument)) {
			$tripBookingDocument->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore Trip Booking Document ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $tripBookingDocument = TripBookingDocument::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $tripBookingDocument)) {
			if(!empty($tripBookingDocument->document_url)){
				Storage::delete($tripBookingDocument->document_url);
			}
			$tripBookingDocument->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response(['status' => 'You do not have permission to Permanent Delete Trip Booking Document ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripBookingDocument = TripBookingDocument::withTrashed()->find($id);
            if(Auth::user()->can('restore', $tripBookingDocument)) {
                $tripBookingDocument->restore();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Restore Trip Booking Document ID: ".$id.""];
            }
        }
		return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these Trip Booking Document ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripBookingDocument = TripBookingDocument::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $tripBookingDocument)) {
				if(!empty($tripBookingDocument->document_url)){
					Storage::delete($tripBookingDocument->document_url);
				}
                $tripBookingDocument->forceDelete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Permanent Delete Trip Booking Document ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these Trip Booking Document ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
