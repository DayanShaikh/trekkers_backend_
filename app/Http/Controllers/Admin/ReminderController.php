<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ReminderController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(Reminder::class, 'reminder');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Reminder::with('trip', 'trip.location')
       ->when('trip', function ($q) use($request){
        if($request->trip_id) {
            $q->where('trip_id', $request->trip_id);
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
        ->orderBy($request->order_by ?? 'email', $request->order ?? 'desc')
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
            'trip_id' => ['int', 'required'],             
            'email'   =>  ['string','required'],
        ]);
        $data['creater_id'] =   auth()->user()->id;
        $reminder = Reminder::create($data);        
        return response()->json([
            'status'    =>  true,
            'reminder' => $reminder,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Http\Response
     */
    public function show(Reminder $reminder)
    {
        return  response()->json(['status' => true, 'reminder' => $reminder]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reminder $reminder)
    {
        $data = $request->validate([
            'trip_id' => ['int', 'required'],             
            'email'   =>  ['string','required'],
        ]);
        $reminder->update($data);        
        return response()->json([
            'status'    =>  true,
            'reminder' => $reminder,
        ]);
    }

     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TripType  $tripType
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, Reminder $reminder)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        
        $reminder->update(['status' => $request->boolean('status')]);
        $reminder->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reminder $reminder)
    {
        $reminder->delete();
        return response()->json([
			'status' => true,
            'message'   =>  'Record has been deleted'
		]);
    }

    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $reminder = Reminder::find($id);
            if(Auth::user()->can('delete', $reminder)) {
                $reminder->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these reminders ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $reminder = Reminder::withTrashed()->find($id);
		if(Auth::user()->can('restore', $reminder)) {
			$reminder->restore();
			return response()->json([
                'status' => true,
                'message'   =>  'Record has been restored',
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore reminder ID: '.$id,
            ], 403);
		}
    }
    public function forceDelete($id)
    {
        $reminder = Reminder::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $reminder)) {
			$reminder->forceDelete();
			return response()->json([
                'status' => true,
                'message'   =>  'Record has been permanent deleted',
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete this reminder ID: '.$id], 403);
		}
    }
    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $reminder = Reminder::withTrashed()->find($id);
            if(Auth::user()->can('restore', $reminder)) {
                $reminder->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these reminders ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $reminder = Reminder::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $reminder)) {
                $reminder->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these page galleries ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
