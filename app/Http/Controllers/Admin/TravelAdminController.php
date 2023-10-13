<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TravelAdmin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class TravelAdminController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(TravelAdmin::class, 'travel_admin');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TravelAdmin::query()
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
            'name' => ['string','required'],          
            'email'  =>  ['string','required','unique:travel_admins,email'],
            'password'  =>  ['string','required'],
        ]);
        $data['creater_id'] =   auth()->user()->id;
        $travelAdmin = TravelAdmin::create($data);
        return response()->json([
            'status'    =>  true,
            'travelAdmin' => $travelAdmin,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TravelAdmin  $travelAdmin
     * @return \Illuminate\Http\Response
     */
    public function show(TravelAdmin $travelAdmin)
    {
        return response()->json(['status' => true, 'travelAdmin' => $travelAdmin]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TravelAdmin  $travelAdmin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TravelAdmin $travelAdmin)
    {
        $data = $request->validate([
            'name' => ['string','required'],          
            'email'  =>  ['string','required', Rule::unique('travel_admins')->ignore($travelAdmin)],
            'password'  =>  ['string','required'],
        ]);
        $travelAdmin->update($data);
        return response()->json([
            'status'    =>  true,
            'travelAdmin' => $travelAdmin,
        ]);
    }

    /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TravelAdmin  $travelAdmin
     * @return \Illuminate\Http\Response
     */ 
    public function updateActiveStatus(Request $request, TravelAdmin $travelAdmin)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        $travelAdmin->update(['status' => $request->boolean('status')]);
        $travelAdmin->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TravelAdmin  $travelAdmin
     * @return \Illuminate\Http\Response
     */
    public function destroy(TravelAdmin $travelAdmin)
    {
        $travelAdmin->delete();
        return response()->json([
			'status' => true,
		]);
    }

    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $travelAdmin = TravelAdmin::find($id);
            if(Auth::user()->can('delete', $travelAdmin)) {
                $travelAdmin->delete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to delete Travel Admin ID: ".$id.""];
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
        $travelAdmin = TravelAdmin::withTrashed()->find($id);
		if(Auth::user()->can('restore', $travelAdmin)) {
			$travelAdmin->restore();
			return response()->json(['status' => true]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore Travel Admin ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $travelAdmin = TravelAdmin::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $travelAdmin)) {
			$travelAdmin->forceDelete();
			return response()->json(['status' => true]);
		}
		else {
			return response(['status' => 'You do not have permission to Permanent Delete Travel Admin ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $travelAdmin = TravelAdmin::withTrashed()->find($id);
            if(Auth::user()->can('restore', $travelAdmin)) {
                $travelAdmin->restore();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Restore Travel Admin ID: ".$id.""];
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
            $travelAdmin = TravelAdmin::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $travelAdmin)) {
                $travelAdmin->forceDelete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Permanent Delete Travel Admin ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors,
        ]);
	}
}
