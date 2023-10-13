<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TripType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class TripTypeController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(TripType::class, 'trip_type');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TripType::query()
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
            'title' => ['string', 'required','unique:trip_types'],
            'show_on_homepage'   =>  [''],
            'sortorder'   =>  [''],
            'description'   =>  [''],
        ]);
        $data['creater_id'] =   auth()->user()->id;
        $tripType = TripType::create($data);
        if ($request->hasFile('image')){
			if(!empty($tripType->image)){
				Storage::delete($tripType->image);
			}
			$tripType->image = Storage::putFile('public/trip-types', $request->file('image'));
		}
        $tripType->save();
        return response()->json([
            'status'    =>  true,
            'tripType' => $tripType,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripType  $tripType
     * @return \Illuminate\Http\Response
     */
    public function show(TripType $tripType)
    {
        return  response()->json(['status' => true, 'tripType' => $tripType]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripType  $tripType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TripType $tripType)
    {
        $data = $request->validate([
            'title' => ['string','required', Rule::unique('trip_types')->ignore($tripType)],
            'show_on_homepage'   =>  [''],
            'sortorder'   =>  [''],
            'description'   =>  [''],
        ]);

        $tripType->update($data);
        if ($request->hasFile('image')){
			if(!empty($tripType->image)){
				Storage::delete($tripType->image);
			}
			$tripType->image = Storage::putFile('public/trip-types', $request->file('image'));
		}
        $tripType->save();
        return response()->json([
            'status'    =>  true,
            'tripType' => $tripType,
        ]);
    }

    /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripType  $tripType
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, TripType $tripType)
	{
		$validated = $request->validate([
			'status' => ['required'],
		]);

        $tripType->update(['status' => $request->boolean('status')]);
        $tripType->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripType  $tripType
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripType $tripType)
    {
        $tripType->delete();
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
            $tripType = TripType::find($id);
            if(Auth::user()->can('delete', $tripType)) {
                $tripType->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these trip types ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $tripType = TripType::withTrashed()->find($id);
		if(Auth::user()->can('restore', $tripType)) {
			$tripType->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored']);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore Trip Type ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $tripType = TripType::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $tripType)) {
            if(!empty($tripType->image)){
                Storage::delete($tripType->image);
            }
			$tripType->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to Permanent Delete Trip Type ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripType = TripType::withTrashed()->find($id);
            if(Auth::user()->can('restore', $tripType)) {
                $tripType->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these trip types ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripType = TripType::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $tripType)) {
                if(!empty($tripType->image)){
                    Storage::delete($tripType->image);
                }
				$tripType->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these trip types ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
