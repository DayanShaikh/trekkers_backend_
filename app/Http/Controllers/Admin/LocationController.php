<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(Location::class, 'location');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Location::query()->with('destination')
        ->when($request->get('destination_id'), function ($q) use($request){
                $q->where('destination_id', $request->destination_id );
        })
		->when($request->get('has_flight') != '', function ($query) use($request){
			$query->where("has_flight", $request->get('has_flight'));
		})
		->when($request->get('show_trip_letter') != '', function ($query) use($request){
			$query->where("show_trip_letter", $request->get('show_trip_letter'));
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
        ->orderBy($request->order_by ?? 'title', $request->order ?? 'asc')
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
            'destination_id' =>  ['int', 'required'],
            'title'  =>  ['string','required', 'unique:locations'],
            'trip_letter'  =>  [''],
            'show_trip_letter'  =>  [''],
			'trip_fee'  =>  [''],
            'travel_time'   =>  [''],
            'upsell_email_title'  =>  [''],
            'upsell_email_content'  =>  [''],
            'upsell_email_title2'  =>  [''],
            'upsell_email_content2'  =>  [''],
            'has_flight'  =>  [''],
            'icons'  =>  [''],
            'require_passport_details'  =>  [''],
            'trip_level'  =>  [''],
			'included'  =>  [''],
			'travel_information'  =>  [''],
			'program_details'  =>  [''],
			'packing_list'  =>  [''],
			'faqs'  =>  [''],
			'faqs_new'  =>  [''],
			'reviews'  =>  [''],
			'review_text'  =>  [''],
			'listing_title'  =>  [''],
			'listing_text'  =>  [''],
			'marketing_text' => [''],
			'excursions'  =>  [''],
			'combination'  =>  [''],
			'flight'  =>  [''],
			'meals'  =>  [''],
			'min_people'  =>  [''],
			'baggage'  =>  [''],
            'sortorder'  =>  ['int', 'required'],
			'dropbox_link'  =>  [''],
			'dropbox_link_gids'  =>  [''],
            'dropbox_folder_name' => ['']
        ]);

        $data['creater_id'] =  auth()->user()->id;
        $data['icons'] = explode(',', $request->icons);
        $location = Location::create($data);
        if($request->trip_type_id){
            $location->tripTypes()->sync(explode(',', $request->trip_type_id));
        }
		if($request->attribute_id){
            $location->attributes()->sync(explode(',', $request->attribute_id));
        }
        if ($request->hasFile('listing_image')){
			if(!empty($location->listing_image)){
				Storage::delete($location->listing_image);
			}
			$location->listing_image = Storage::putFile('public/locations', $request->file('listing_image'));
		}
        $location->save();
        return response()->json([
            'status'    =>  true,
            'location' => $location,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function show(Location $location)
    {
        $location = $location->with('tripTypes', 'attributes', 'destination')->where('id', $location->id)->first();
        return response()->json(['status' => true, 'location' => $location]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Location $location)
    {
        $data = $request->validate([
            'destination_id' =>  ['int', 'required'],
            'title'  =>  ['string','required', Rule::unique('locations')->ignore($location)],
            'trip_letter'  =>  [''],
            'show_trip_letter'  =>  [''],
			'trip_fee'  =>  [''],
            'travel_time'   =>  [''],
            'upsell_email_title'  =>  [''],
            'upsell_email_content'  =>  [''],
            'upsell_email_title2'  =>  [''],
            'upsell_email_content2'  =>  [''],
            'has_flight'  =>  [''],
            'icons'  =>  [''],
            'require_passport_details'  =>  [''],
            'trip_level'  =>  [''],
			'included'  =>  [''],
			'travel_information'  =>  [''],
			'program_details'  =>  [''],
			'packing_list'  =>  [''],
			'faqs'  =>  [''],
			'faqs_new'  =>  [''],
			'reviews'  =>  [''],
			'review_text'  =>  [''],
			'listing_title'  =>  [''],
			'listing_text'  =>  [''],
			'marketing_text' => [''],
			'excursions'  =>  [''],
			'combination'  =>  [''],
			'flight'  =>  [''],
			'meals'  =>  [''],
			'min_people'  =>  [''],
			'baggage'  =>  [''],
            'sortorder'  =>  ['int', 'required'],
			'dropbox_link'  =>  [''],
			'dropbox_link_gids'  =>  [''],
            'dropbox_folder_name' => ['']
        ]);
        $data['icons'] = explode(',', $request->icons);
        if($request->trip_type_id){
            $location->tripTypes()->sync(explode(',', $request->trip_type_id));
        }
		if($request->attribute_id){
            $location->attributes()->sync(explode(',', $request->attribute_id));
        }
        $location->update($data);
		if ($request->hasFile('listing_image')){
			if(!empty($location->listing_image)){
				Storage::delete($location->listing_image);
			}
			$location->listing_image = Storage::putFile('public/locations', $request->file('listing_image'));
		}
        $location->save();
        return response()->json([
            'status'    =>  true,
            'location' => $location,
        ]);
    }

    /**
     * Update the Lock Staus for location.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, Location $location)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);

        $location->update(['status' => $request->boolean('status')]);
        $location->save();
        return response()->json([
            'status' => true,
            'message' => "Status has been updated"
        ]);
	}
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function destroy(Location $location)
    {
        $location->delete();
        return response()->json([
			'status' => true,
            'message' => 'Record has been deleted'
		]);
    }

    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $location = Location::find($id);
            if(Auth::user()->can('delete', $location)) {
                $location->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these locations. ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }
    public function restore($id)
    {
        $location = Location::withTrashed()->find($id);
		if(Auth::user()->can('restore', $location)) {
			$location->restore();
			return response()->json([
                'status' => true,
                'message' => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this location ID: '.$id,
            ], 403);
		}
    }
    public function forceDelete($id)
    {
        $location = Location::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $location)) {
            if(!empty($location->listing_image)){
                Storage::delete($location->listing_image);
            }
			$location->forceDelete();
			return response()->json([
                'status' => true,
                'message' => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error'  =>  'You do not have permission to permanent delete this location. ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [] ;
        foreach($request->get('ids') as $id) {
            $location = Location::withTrashed()->find($id);
            if(Auth::user()->can('restore', $location)) {
                $location->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these location ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}
    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $location = Location::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $location)) {
				if($location->listing_image){
                    Storage::delete($location->listing_image);
                }
				$location->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these locations ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}

    public function getLocationPage(Location $location)
	{
        $page = $location->page;
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

    public function updateLocationPage(Request $request, Location $location)
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

        $hasPage = $location->page;
        if($hasPage){
            $location->page()->update($data);
            if ($request->hasFile('image')){
                if(!empty($hasPage->image)){
                    Storage::delete($hasPage->image);
                }
                $hasPage->image = Storage::putFile('public/pages', $request->file('image'));
                $hasPage->save();
            }
        }
        else{
            $hasPage = $location->page()->create($data);
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
