<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Page;
use Illuminate\Support\Facades\Storage;

class DestinationController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(Destination::class, 'destination');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Destination::query()
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
				//$query->where('status', false);
            }
        })
        ->when($request->get("status") != null, function($query) use ($request){
            $query->where('status', $request->status );    
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
            'title' => ['string','required'],
            'iso_code' => [''],
            'travel_insurance_fees' =>  ['int'],
            'is_survival_adventure_insurance_active'   =>  [''],
			'intro_title' =>  [''],
            'intro_text' =>  [''],
            'intro_video' =>  [''],
            'video_text' =>  [''],
            'header_video_id' =>  [''],
            'trip_title' =>  [''],
            'other_trip_title' =>  [''],
            'trip_toggle' =>  [''],
            'sortorder'  =>  ['int','required'],
            'color_code' => [''],
            'unicode' => [''],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $destination = Destination::create($data);
		
		if(isset($request->trips) && !empty($request->trips)){
			$trips = explode(",",$request->trips);
			foreach ($trips as $trip) { 
				//return $trip;
				$pivot[$trip] = ['type' => 0]; 
			}
			$destination->trips()->sync($pivot);
		}
		if(isset($request->other_trips) && !empty($request->other_trips)){
			$otherTrips = explode(",",$request->other_trips);
			foreach ($otherTrips as $otherTrip) { 
				//return $trip;
				$pivot[$otherTrip] = ['type' => 1]; 
			}
			$destination->trips()->sync($pivot);
		}
		if ($request->hasFile('thumb_image')){
			if(!empty($destination->thumb_image)){
				Storage::delete($destination->thumb_image);
			}
			$destination->thumb_image = Storage::putFile('public/destinations', $request->file('thumb_image'));
        }
		$destination->save();
        return response()->json([
            'status'    =>  true,
            'destination' => $destination,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Destination  $destination
     * @return \Illuminate\Http\Response
     */
    public function show(Destination $destination)
    {
		$destination = $destination->with('headerVideo', 'trips', 'otherTrips')->where('id', $destination->id)->first();
        return response()->json(['status' => true, 'destination' => $destination]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Destination  $destination
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, Destination $destination)
    {
        $data = $request->validate([
            'title' => ['string','required'],
            'iso_code' => [''],
            'travel_insurance_fees' =>  [''],
            'is_survival_adventure_insurance_active'   =>  [''],
			'intro_title' =>  [''],
            'intro_text' =>  [''],
            'intro_video' =>  [''],
            'video_text' =>  [''],
            'header_video_id' =>  [''],
            'trip_title' =>  [''],
            'other_trip_title' =>  [''],
            'trip_toggle' =>  [''],
            'sortorder'  =>  ['int','required'],
            'color_code' => [''],
            'unicode' => [''],
        ]);
		if(isset($request->trips) && !empty($request->trips)){
			$trips = explode(",",$request->trips);
			foreach ($trips as $trip) { 
				//return $trip;
				$pivot[$trip] = ['type' => 0]; 
			}
			$destination->trips()->sync($pivot);
		}
		if(isset($request->other_trips) && !empty($request->other_trips)){
			$otherTrips = explode(",",$request->other_trips);
			foreach ($otherTrips as $otherTrip) { 
				//return $trip;
				$pivot[$otherTrip] = ['type' => 1]; 
			}
			$destination->trips()->sync($pivot);
		}
        $destination->update($data);
		if ($request->hasFile('thumb_image')){
			if(!empty($destination->thumb_image)){
				Storage::delete($destination->thumb_image);
			}
			$destination->thumb_image = Storage::putFile('public/destinations', $request->file('thumb_image'));
        }
		$destination->save();
        return response()->json([
            'status'    =>  true,
            'destination' => $destination,
        ]);
    }

    /**
	 * Update the Lock Status for destination
	 *
	 * @param  \App\Models\Destination  $destination
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function updateActiveStatus(Request $request, Destination $destination)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        if(Auth::user()->can('update', $destination)){
            $destination->update(['status' => $request->boolean('status')]);
            $destination->save();
            return response()->json([
                'status' => true,
                'message' => "Status has been updated"
            ]);
        }
        else{
            return response()->json([
                'status' => true,
                'message' => "You do not has permission to update this status"
            ]);
        }
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Destination  $destination
     * @return \Illuminate\Http\Response
     */
    public function destroy(Destination $destination)
    {
        $destination->delete();
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
            $destination = Destination::find($id);
            if(Auth::user()->can('delete', $destination)) {
                $destination->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these destination ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $destination = Destination::withTrashed()->find($id);
		if(Auth::user()->can('restore', $destination)) {
			$destination->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this destination ID: '.$id,
            ], 403);
		}
    }
    public function forceDelete($id)
    {
        $destination = Destination::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $destination)) {
			if($destination->thumb_image){
                Storage::delete($destination->thumb_image);
            }
			$destination->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent this delete destination ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $destination = Destination::withTrashed()->find($id);
            if(Auth::user()->can('restore', $destination)) {
                $destination->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these destination ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $destination = Destination::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $destination)) {
				if($destination->thumb_image){
                    Storage::delete($destination->thumb_image);
                }
                $destination->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these destination ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}

    public function getDestinationPage(Destination $destination)
	{
        $page = $destination->page;
        if($page){
            return response()->json([
				"status" => true,
                'page' => $page,
            ]);
        }
        return response()->json([
            "status" => false,
            "message" => 'Page not found'
        ]);
    }

    public function updateDestinationPage(Request $request, Destination $destination)
	{
        $data = $request->validate([
            'page_name' => ['string'],
            'title' =>  [''],
            'content'   =>  [''],
            'highlights'    =>  [''],
            'meta_title'    =>  [''],
            'meta_description'    =>  [''],
            'meta_keywords'    =>  [''],
            'sitemap_title'    =>  [''],
            'sitemap_details'    =>  [''],
            'header_details'    =>  [''],
            'show_schema_markup'    =>  [''],
            'schema_title'    =>  [''],
			'show_search_box' => ['']
        ]);
        $hasPage = $destination->page;
        if($hasPage){
            $destination->page()->update($data);
            if ($request->hasFile('image')){
                if(!empty($hasPage->image)){
                    Storage::delete($hasPage->image);
                }
                $hasPage->image = Storage::putFile('public/pages', $request->file('image'));
                $hasPage->save();
            }
        }
        else{
            $hasPage = $destination->page()->create($data);
            if ($request->hasFile('image')){
                if(!empty($hasPage->image)){
                    Storage::delete($hasPage->image);
                }
                $hasPage->image = Storage::putFile('public/pages', $request->file('image'));
                $hasPage->save();
            }
        }
        return response()->json([
            'status' => true,
            'page' => $hasPage,
        ]);
    }

}
