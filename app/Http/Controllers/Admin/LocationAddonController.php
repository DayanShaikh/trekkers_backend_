<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\LocationAddon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LocationAddonController extends Controller
{
     /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(LocationAddon::class, 'location_addon');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return LocationAddon::with('location')
         ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })
        ->when($request->get("status") != null, function($query) use ($request){
            $query->where('status', $request->status );    
        })
		->when($request->get('location_id'), function ($query) use($request){
			$query->where("location_id", $request->get('location_id'));
		})
         ->filter($request->only('search'))
         ->orderBy($request->order_by ?? 'title', $request->order ?? 'asc')
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
            'title'   =>  ['required'],
            'description' =>  [''],
            'price' =>  [''],
            'is_public' =>  ['required'],
            'hide_payment' =>  [''],
            'sortorder' =>  [''],
            'extra_field_1' =>  [''],
            'extra_field_2' =>  [''],
            'extra_field_3' =>  [''],

        ]);
        $data['creater_id'] =   auth()->user()->id;
        $locationAddon = LocationAddon::create($data);
        if ($request->hasFile('image')){
			if(!empty($locationAddon->image)){
				Storage::delete($locationAddon->image);
			}
			$locationAddon->image = Storage::putFile('public/location-addons/image', $request->file('image'));
        }
        if ($request->hasFile('mobile_image')){
			if(!empty($locationAddon->mobile_image)){
				Storage::delete($locationAddon->mobile_image);
			}
			$locationAddon->mobile_image = Storage::putFile('public/location-addons/mobile-image', $request->file('mobile_image'));
        }
        $locationAddon->save();
        return response()->json([
            'status'    =>  true,
            'locationAddon' => $locationAddon,
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LocationAddon  $locationAddon
     * @return \Illuminate\Http\Response
     */
    public function show(LocationAddon $locationAddon)
    {
        return response()->json(['status' => true, 'locationAddon' => $locationAddon]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LocationAddon  $locationAddon
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LocationAddon $locationAddon)
    {

        $data = $request->validate([
            'location_id'     =>  ['int'],
            'title'   =>  ['required'],
            'description' =>  [''],
            'price' =>  [''],
            'is_public' =>  [''],
            'hide_payment' =>  [''],
            'sortorder' =>  [''],
            'extra_field_1' =>  [''],
            'extra_field_2' =>  [''],
            'extra_field_3' =>  [''],

        ]);

        $locationAddon->update($data);
        if ($request->hasFile('image')){
			if(!empty($locationAddon->image)){
				Storage::delete($locationAddon->image);
			}
			$locationAddon->image = Storage::putFile('public/location-addons/image', $request->file('image'));
		}
        if ($request->hasFile('mobile_image')){
			if(!empty($locationAddon->mobile_image)){
				Storage::delete($locationAddon->mobile_image);
			}
			$locationAddon->mobile_image = Storage::putFile('public/location-addons/mobile-image', $request->file('mobile_image'));
		}
        $locationAddon->save();
        return response()->json([
            'status'    =>  true,
            'locationAddon' => $locationAddon,
        ]);

    }

         /**
     * Update the Lock Staus for locationAddon.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LocationAddon  $locationAddon
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, LocationAddon $locationAddon)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);

        $locationAddon->update(['status' => $request->boolean('status')]);
        $locationAddon->save();
        return response()->json([
            'status' => true,
            'message' => "Status has been updated"
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LocationAddon  $locationAddon
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, LocationAddon $locationAddon)
    {
        $locationAddon->delete();
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
            $locationAddon = LocationAddon::find($id);
            if(Auth::user()->can('delete', $locationAddon)) {
                $locationAddon->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these location addons ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $locationAddon = LocationAddon::withTrashed()->find($id);
		if(Auth::user()->can('restore', $locationAddon)) {
			$locationAddon->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this location addon ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $locationAddon = LocationAddon::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $locationAddon)) {
            if(!empty($locationAddon->image)){
                Storage::delete($locationAddon->image);
            }
            if(!empty($locationAddon->mobile_image)){
                Storage::delete($locationAddon->mobile_image);
            }
			$locationAddon->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete this location addon ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $locationAddon = LocationAddon::withTrashed()->find($id);
            if(Auth::user()->can('restore', $locationAddon)) {
                $locationAddon->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these location addons ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $locationAddon = LocationAddon::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $locationAddon)) {
                if(!empty($locationAddon->image)){
                    Storage::delete($locationAddon->image);
                }
                if(!empty($locationAddon->mobile_image)){
                    Storage::delete($locationAddon->mobile_image);
                }
				$locationAddon->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these location dddons ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
