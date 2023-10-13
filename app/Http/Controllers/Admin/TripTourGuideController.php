<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TripTourGuide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripTourGuideController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(TripTourGuide::class, 'trip_tour_guide');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TripTourGuide::with('trip', 'trip.location')
       ->when($request->get('trip_id'), function ($q) use($request){
			if($request->trip_id) {
				$q->where('trip_id', $request->trip_id );
			}
        })
		->when($request->get('user_id'), function ($query) use($request){
			$query->where("user_id", $request->get('user_id'));
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
			'user_id'     =>  ['int'],
            'trip_id' =>  ['required'],
            'include_in_count'   =>  [''],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $tripTourGuide = TripTourGuide::create($data);
        return response()->json([
            'status'    =>  true,
            'tripTourGuide' => $tripTourGuide,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripTourGuide  $tripTourGuide
     * @return \Illuminate\Http\Response
     */
    public function show(TripTourGuide $tripTourGuide)
    {
        //return $tripTourGuide;
		$tripTourGuide = $tripTourGuide->with('trip', 'trip.location')->where('id', $tripTourGuide->id)->first();
        return response()->json(['status' => true, 'tripTourGuide' => $tripTourGuide]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripTourGuide  $tripTourGuide
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TripTourGuide $tripTourGuide)
    {
        $data = $request->validate([
           'user_id'     =>  ['int'],
            'trip_id' =>  ['required'],
            'include_in_count'   =>  [''],
        ]);
        $tripTourGuide->update($data);
        return response()->json([
            'status'    =>  true,
            'tripTourGuide' => $tripTourGuide,
        ]);
    }

 /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripTourGuide  $tripTourGuide
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, TripTourGuide $tripTourGuide)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        $tripTourGuide->update(['status' => $request->boolean('status')]);
        $tripTourGuide->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripTourGuide  $tripTourGuide
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripTourGuide $tripTourGuide)
    {
        $tripTourGuide->delete();
        return response()->json(['status'=>true]);
    }

    public function massDestroy(Request $request)
    {
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripTourGuide = TripTourGuide::find($id);
            if(Auth::user()->can('delete', $tripTourGuide)) {
                $tripTourGuide->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
			"status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these Trip Tour Guide ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $tripTourGuide = TripTourGuide::withTrashed()->find($id);
		if(Auth::user()->can('restore', $tripTourGuide)) {
			$tripTourGuide->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore Trip Tour Guide ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $tripTourGuide = TripTourGuide::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $tripTourGuide)) {
			$tripTourGuide->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response(['status' => 'You do not have permission to Permanent Delete Trip Tour Guide ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripTourGuide = TripTourGuide::withTrashed()->find($id);
            if(Auth::user()->can('restore', $tripTourGuide)) {
                $tripTourGuide->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these Trip Tour Guide ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripTourGuide = TripTourGuide::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $tripTourGuide)) {
                $tripTourGuide->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
		return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these Trip Tour Guide ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
