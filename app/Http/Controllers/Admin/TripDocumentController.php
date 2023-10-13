<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TripDocument;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TripDocumentController extends Controller
{
      /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(TripDocument::class, 'trip_document');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TripDocument::with('trip')
        ->when($request->trip_id, function ($q) use($request){
            if($request->trip_id) {
                $q->where('trip_id', $request->trip_id );
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
            'trip_id' =>  ['int'],
            'title'   =>  ['string', ' required', 'unique:trip_documents'],
            'sortorder'   =>  [''],

        ]);
        $data['creater_id'] = auth()->user()->id;
        $tripDocument = TripDocument::create($data);
        if ($request->hasFile('document_url')){
			if(!empty($tripDocument->document_url)){
				Storage::delete($tripDocument->document_url);
			}
			$tripDocument->document_url = Storage::putFile('public/trip_documents', $request->file('document_url'));
        }
        $tripDocument->save();
        return response()->json([
            'status'    =>  true,
            'tripDocument' => $tripDocument,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripDocument  $tripDocument
     * @return \Illuminate\Http\Response
     */
    public function show(TripDocument $tripDocument)
    {
        return response()->json(['status' => true, 'tripDocument' => $tripDocument]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripDocument  $tripDocument
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TripDocument $tripDocument)
    {
        $data = $request->validate([
            'trip_id' =>  ['int'],
            'title'   =>  ['string', ' required', Rule::unique('trip_documents')->ignore($tripDocument)],
            'sortorder'   =>  [''],

        ]);
        $tripDocument->update($data);
        if ($request->hasFile('document_url')){
			if(!empty($tripDocument->document_url)){
				Storage::delete($tripDocument->document_url);
			}
			$tripDocument->document_url = Storage::putFile('public/trip_documents', $request->file('document_url'));
        }
        $tripDocument->save();
        return response()->json([
            'status'    =>  true,
            'tripDocument' => $tripDocument,
        ]);
    }

     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripDocument  $tripDocument
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, TripDocument $tripDocument)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);

        $tripDocument->update(['status' => $request->boolean('status')]);
        $tripDocument->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripDocument  $TripDocument
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripDocument $tripDocument)
    {
        $tripDocument->delete();
        return response()->json([
            'status'    =>  true,
            'message'   =>  'Record has been deleted'
        ]);
    }

    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripDocument = TripDocument::find($id);
            if(Auth::user()->can('delete', $tripDocument)) {
				if(!empty($tripDocument->document_url)){
					Storage::delete($tripDocument->document_url);
				}
                $tripDocument->delete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to delete Trip Document ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these Trip Document ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $tripDocument = TripDocument::withTrashed()->find($id);
		if(Auth::user()->can('restore', $tripDocument)) {
			$tripDocument->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore Trip Document ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $tripDocument = TripDocument::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $tripDocument)) {
			if($tripDocument->document_url){
				Storage::delete($tripDocument->document_url);
			}
			$tripDocument->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response(['status' => 'You do not have permission to Permanent Delete Trip Document ID: '.$id], 403);
		}
    }
    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripDocument = TripDocument::withTrashed()->find($id);
            if(Auth::user()->can('restore', $tripDocument)) {
                $tripDocument->restore();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Restore Trip Document ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these Trip Document ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);

	}
    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tripDocument = TripDocument::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $tripDocument)) {
				if($tripDocument->document_url){
					Storage::delete($tripDocument->document_url);
				}
                $tripDocument->forceDelete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Permanent Delete Trip Document ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these Trip Document ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
