<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\UserNote;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserNoteController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(UserNote::class, 'user_note');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return UserNote::select('user_notes.*')
		->with('trip', 'trip.location')
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })
        ->when($request->get("status") != null, function($query) use ($request){
            $query->where('status', $request->status );    
        })
        ->filter($request->only('search'))
        ->orderBy($request->order_by ?? 'sortorder', $request->order ?? 'asc')
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
            'user_id' => ['required'],
            'trip_id'   =>  [''],
			'notes'   =>  [''],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $userNote = UserNote::create($data);
        $userNote->save();
        return response()->json([
            'status'    =>  true,
            'userNote' => $userNote,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserNote  $userNote
     * @return \Illuminate\Http\Response
     */
    public function show(UserNote $userNote)
    {
		$userNote = $userNote->with('trip', 'trip.location')->where('id', $userNote->id)->first();
        return response()->json(['status' => true, 'userNote' => $userNote]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserNote  $userNote
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserNote $userNote)
    {
        $data = $request->validate([
            'user_id' => ['required'],
            'trip_id'   =>  [''],
			'notes'   =>  [''],
        ]);
        $userNote->update($data);
        $userNote->save();
        return response()->json([
            'status'    =>  true,
            'userNote' => $userNote,
        ]);
    }

     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserNote  $userNote
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, UserNote $userNote)
	{
		$validated = $request->validate([
			'status' => ['required'],
		]);

		$userNote->update(['status' => $request->boolean('status')]);
		$userNote->save();

		return response()->json(['status' => true, 'message' => "Status has been updated"]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserNote  $userNote
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserNote $userNote)
    {
        $userNote->delete();
		return response()->json([
			'status' => true,
            "message" => 'Record has been deleted'
		]);
    }

    public function massDestroy (Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $userNote = UserNote::find($id);
            if(Auth::user()->can('delete', $userNote)) {
                $userNote->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these user note ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
	}

    public function restore($id)
    {
        $userNote = UserNote::withTrashed()->find($id);
		if(Auth::user()->can('restore', $userNote)) {
			$userNote->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this user note ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $userNote = UserNote::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $userNote)) {
			$userNote->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete this user note ID: '.$id], 403);
		}
    }
    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $userNote = UserNote::withTrashed()->find($id);
            if(Auth::user()->can('restore', $userNote)) {
                $userNote->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these sttributes ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $userNote = UserNote::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $userNote)) {
                $userNote->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanant delete these user notes ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
