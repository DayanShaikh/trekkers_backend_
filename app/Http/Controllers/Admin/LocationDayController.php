<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\LocationDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LocationDayController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(LocationDay::class, 'location_day');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return LocationDay::with('location')
        ->when('location', function ($q) use($request){
            if($request->location_id) {
                $q->where('location_id', $request->location_id );
            }
        })
        ->when($request->get('page_type'), function ($query) use($request){
			$query->where("page_type", $request->get('page_type'));
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
            'location_id' => ['required','int'],
            'title' =>  ['required'],
            'description'  =>  [''],
            'sortorder'   =>  [''],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $locationDay = LocationDay::create($data);
        if ($request->hasFile('image')){
			if(!empty($locationDay->image)){
				Storage::delete($locationDay->image);
			}
			$locationDay->image = Storage::putFile('public/location-days', $request->file('image'));
            $locationDay->save();
        }
        return response()->json([
            'status'    =>  true,
            'locationDay' => $locationDay,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LocationDay  $locationDay
     * @return \Illuminate\Http\Response
     */
    public function show(LocationDay $locationDay)
    {
        return response()->json(['status' => true, 'locationDay' => $locationDay]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LocationDay  $locationDay
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LocationDay $locationDay)
    {
        $data = $request->validate([
            'location_id' => ['required','int'],
            'title' =>  ['required'],
            'description'  =>  [''],
            'sortorder'   =>  [''],
        ]);
        $locationDay->update($data);
        if ($request->hasFile('image')){
			if(!empty($locationDay->image)){
				Storage::delete($locationDay->image);
			}
			$locationDay->image = Storage::putFile('public/location-days', $request->file('image'));
            $locationDay->save();
        }
        return response()->json([
            'status'    =>  true,
            'locationDay' => $locationDay,
        ]);
    }

	/**
	 * Update the Lock Status for locationDay.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Models\LocationDay  $locationDay
	 * @return \Illuminate\Http\Response
	 */
	public function updateActiveStatus(Request $request, LocationDay $locationDay)
	{
		$validated = $request->validate([
			'status' => ['required'],
		]);
		$locationDay->update(['status' => $request->boolean('status')]);
		$locationDay->save();
		return response()->json([
			'status' => true,
			'message'   =>  'Status has been updated'
		]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LocationDay  $locationDay
     * @return \Illuminate\Http\Response
     */
    public function destroy(LocationDay $locationDay)
    {
        $locationDay->delete();
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
            $locationDay = LocationDay::find($id);
            if(Auth::user()->can('delete', $locationDay)) {
                $locationDay->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these location days ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function GetDataByPageId($id)
    {
        return LocationDay::with('location_day')
        ->where('location_id',$id)
        ->get();
    }

    public function restore($id)
    {
        $locationDay = LocationDay::withTrashed()->find($id);
		if(Auth::user()->can('restore', $locationDay)) {
			$locationDay->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this location day ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $locationDay = LocationDay::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $locationDay)) {
			if(!empty($locationDay->image)){
                Storage::delete($locationDay->image);
            }
			$locationDay->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete this location day ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $locationDay = LocationDay::withTrashed()->find($id);
            if(Auth::user()->can('restore', $locationDay)) {
                $locationDay->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent restore these location day ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $locationDay = LocationDay::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $locationDay)) {
                if(!empty($locationDay->image)){
                    Storage::delete($locationDay->image);
                }
				$locationDay->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these location day ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}

}
