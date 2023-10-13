<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\LocationPickup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationPickupController extends Controller
{
     /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(LocationPickup::class, 'location_pickup');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return LocationPickup::with('location')
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })
		->when($request->get('location_id'), function ($query) use($request){
			$query->where("location_id", $request->get('location_id'));
		})
        ->when($request->get("status") != null, function($query) use ($request){
            $query->where('status', $request->status );    
        })
        ->filter($request->only('search'))
        ->orderBy($request->order_by ?? 'place', $request->order ?? 'desc')
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
            'location_id'     =>  ['int'],
            'place'   =>  ['required'],
            'time'   =>  ['required'],
            'spot'  =>  ['required'],
            'sortorder' =>  [''],
           ]);

           $data['creater_id']  =   auth()->user()->id;
           $locationPickup = LocationPickup::create($data);
           return response()->json([
            'status'    =>  true,
            'locationPickup' => $locationPickup,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LocationPickup  $locationPickup
     * @return \Illuminate\Http\Response
     */
    public function show(LocationPickup $locationPickup)
    {
        return response()->json(['status' => true, 'locationPickup' => $locationPickup]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LocationPickup  $locationPickup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LocationPickup $locationPickup)
    {
        $data = $request->validate([
            'location_id'     =>  ['int'],
            'place'   =>  ['required'],
            'time'   =>  ['required'],
            'spot'  =>  ['required'],
            'sortorder' =>  [''],
           ]);

        $locationPickup->update($data);
        return response()->json([
            'status'    =>  true,
            'locationPickup' => $locationPickup
        ]);

    }

    	/**
     * Update the Lock Status for age group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LocationPickup  $locationPickup
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, LocationPickup $locationPickup)
    {
        $validated = $request->validate([
			'status' => ['required'],
		]);

		$locationPickup->update(['status' => $request->boolean('status')]);
		$locationPickup->save();

		return response()->json(['status' => true, 'message' => "Status has been updated"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LocationPickup  $locationPickup
     * @return \Illuminate\Http\Response
     */
    public function destroy(LocationPickup $locationPickup)
    {
        $locationPickup->delete();
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
            $locationPickup = LocationPickup::find($id);
            if(Auth::user()->can('delete', $locationPickup)) {
                $locationPickup->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these location pickups ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $locationPickup = LocationPickup::withTrashed()->find($id);
		if(Auth::user()->can('restore', $locationPickup)) {
			$locationPickup->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this location pickup ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $locationPickup = LocationPickup::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $locationPickup)) {
			$locationPickup->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response(['status' => 'You do not have permission to permanent delete this location pickup ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $locationPickup = LocationPickup::withTrashed()->find($id);
            if(Auth::user()->can('restore', $locationPickup)) {
                $locationPickup->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these location pickups ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $locationPickup = LocationPickup::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $locationPickup)) {
                $locationPickup->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these location pickups ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
