<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TripGroup;
use Illuminate\Http\Request;

class TripGroupController extends Controller
{

		/**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->authorizeResource(TripGroup::class, 'trip_group');
	}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TripGroup::query()
		->with(['location', 'trips', 'trips.location'])
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })
        ->when($request->get("status") != null, function($query) use ($request){
            $query->where('status', $request->status );    
        })
        ->filter($request->only('search'))
        ->orderBy($request->order_by ?? 'id', $request->order ?? 'desc')
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
            'location_id' =>  ['required'],
            'trips'   =>  ['required'],

        ]);
        $data['creater_id'] = auth()->user()->id;
        $tripGroup = TripGroup::create($data);
        if($request->get('trips')){
            $tripGroup->trips()->sync($request->get('trips'));
        }
        return response()->json([
            'status'    =>  true,
            'tripGroup' => $tripGroup,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripGroup  $tripGroup
     * @return \Illuminate\Http\Response
     */
    public function show(TripGroup $tripGroup)
    {
        $tripGroup = $tripGroup->with('location','trips', 'trips.location')->where('id', $tripGroup->id)->first();
        return response()->json(['status' => true, 'tripGroup' => $tripGroup]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripGroup  $tripGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TripGroup $tripGroup)
    {
        $data = $request->validate([
            'location_id' =>  ['required'],
            'trips'   =>  ['required'],
        ]);
        $tripGroup->update($data);
        if($request->get('trips')){
            $tripGroup->trips()->sync($request->get('trips'));
        }
        $tripGroup->save();
        return response()->json([
            'status'    =>  true,
            'tripGroup' => $tripGroup,
        ]);
    }

    /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripGroup  $tripGroup
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, TripGroup $tripGroup)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        $tripGroup->update(['status' => $request->boolean('status')]);
        $tripGroup->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripGroup  $tripGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripGroup $tripGroup)
    {
        $tripGroup->delete();
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
            $tripGroup = TripGroup::find($id);
            if(Auth::user()->can('delete', $tripGroup)) {
                $tripGroup->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these trip groups ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $tripGroup = TripGroup::withTrashed()->find($id);
		if(Auth::user()->can('restore', $tripGroup)) {
			$tripGroup->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this trip group ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $tripGroup = TripGroup::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $tripGroup)) {
			$tripGroup->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete this trip group ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripGroup = TripGroup::withTrashed()->find($id);
            if(Auth::user()->can('restore', $tripGroup)) {
                $tripGroup->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these trip groups ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripGroup = TripGroup::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $tripGroup)) {
                $tripGroup->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these trip groups ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
