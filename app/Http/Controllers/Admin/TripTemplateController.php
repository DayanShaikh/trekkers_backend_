<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TripTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripTemplateController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(TripTemplate::class, 'trip_template');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TripTemplate::with('location')
        ->when('location_id', function ($q) use($request){
            if($request->location_id) {
                $q->where('location_id', $request->location_id );
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
        ->filter($request->only('search'))
        ->orderBy($request->order_by ?? 'name', $request->order ?? 'desc')
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
            'location_id' => ['int','required'],
            'name' =>  ['string','required'],
            'content'   =>  ['string','required'],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $tripTemplate = TripTemplate::create($data);
        return response()->json([
            'status'    =>  true,
            'tripTemplate' => $tripTemplate,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripTemplate  $tripTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(TripTemplate $tripTemplate)
    {
        return response()->json(['status' => true, 'tripTemplate' => $tripTemplate]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripTemplate  $tripTemplate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TripTemplate $tripTemplate)
    {
        $data = $request->validate([
            'location_id' => ['int','required'],
            'name' =>  ['string','required'],
            'content'   =>  ['string','required'],

        ]);
        $tripTemplate->update($data);
        return response()->json([
            'status'    =>  true,
            'tripTemplate' => $tripTemplate,
        ]);
    }

    /**
     * Update the Locak Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripTemplate  $tripTemplate
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, TripTemplate $tripTemplate)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);

        $tripTemplate->update(['status' => $request->boolean('status')]);
        $tripTemplate->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripTemplate  $tripTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripTemplate $tripTemplate)
    {
        $tripTemplate->delete();
        return response()->json([
			'status' => true,
		]);
    }

    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripTemplate = TripTemplate::find($id);
            if(Auth::user()->can('delete', $tripTemplate)) {
                $tripTemplate->delete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to delete Trip Template ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors
        ]);
    }

    public function restore($id)
    {
        $tripTemplate = TripTemplate::withTrashed()->find($id);
		if(Auth::user()->can('restore', $tripTemplate)) {
			$tripTemplate->restore();
			return response()->json(['status' => true]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore Trip Template ID: '.$id,
            ], 403);
		}
    }
    public function forceDelete($id)
    {
        $tripTemplate = TripTemplate::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $tripTemplate)) {
			$tripTemplate->forceDelete();
			return response()->json(['status' => true]);
		}
		else {
			return response(['status' => 'You do not have permission to Permanent Delete Trip Template ID: '.$id], 403);
		}
    }

    public function massRestore($id)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripTemplate = TripTemplate::withTrashed()->find($id);
            if(Auth::user()->can('restore', $tripTemplate)) {
                $tripTemplate->restore();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Restore Trip Template ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors
        ]);
	}
    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripTemplate = TripTemplate::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $tripTemplate)) {
                $tripTemplate->forceDelete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Permanent Delete Trip Template ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors,
        ]);
	}
}
