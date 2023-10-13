<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TravelAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TravelAgentController extends Controller
{
     /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(TravelAgent::class, 'travel_agent');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TravelAgent::query()
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })  
        ->when($request->get("status") != null, function($query) use ($request){
            $query->where('status', $request->status );    
        })
        ->filter($request->only('search'))
			->orderBy($request->order_by ?? 'firstname', $request->order ?? 'desc')
			->paginate($request->per_page ?? 10)
			;;
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
            'firstname' =>  ['string', 'required'],
            'lastname' =>  ['string', 'required'],
            'email' =>  ['string','required','unique:travel_agents,email'],
            'username' =>  ['string','required','unique:travel_agents,username'],
            'password' =>  ['string','required'],
            'travelclub_number' =>  ['string']
        ]);

        $data['craeter_id'] = auth()->user()->id;
        $travelAgent = TravelAgent::create($data);
        return response()->json([
            'status'    =>  true,
            'travelAgent'   =>  $travelAgent,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TravelAgent  $travelAgent
     * @return \Illuminate\Http\Response
     */
    public function show(TravelAgent $travelAgent)
    {
        return response()->json(['status' => true, 'travelAgent' => $travelAgent]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TravelAgent  $travelAgent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TravelAgent $travelAgent)
    {
        $data = $request->validate([
            'firstname' =>  ['string','required'],
            'lastname' =>  ['string','required'],
            'email' =>  ['string','required', Rule::unique('travel_agents')->ignore($travelAgent->id)],
            'username' =>  ['string', 'required', Rule::unique('travel_agents')->ignore($travelAgent->id)],
            'password' =>  ['string', 'required'],
            'travelclub_number' =>  ['string']
        ]);

        $travelAgent->update($data);
        return response()->json([
            'status'    =>  true,
            'travelAgent'   =>  $travelAgent,
        ]);
    }

     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TravelAgent  $travelAgent
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, TravelAgent $travelAgent)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        $travelAgent->update(['status' => $request->boolean('status')]);
        $travelAgent->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TravelAgent  $travelAgent
     * @return \Illuminate\Http\Response
     */
    public function destroy(TravelAgent $travelAgent)
    {
        $travelAgent->delete();
		return response()->json([
			'status' => true,
            'message'   =>  'Record has been deleted',
		]);
    }

    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $travelAgent = TravelAgent::find($id);
            if(Auth::user()->can('delete', $travelAgent)) {
                $travelAgent->delete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to delete Travel Agent ID: ".$id.""];
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
        $travelAgent = withTrashed()->find($id);
        if(Auth::user()->can('restore', $travelAgent)){
            $travelAgent->restore();
            return response()->json(['status' => true]);
        }
        else{
            return response([
                'status' => false,
                'error' => 'You do not have permission to restore User ID: '.$id,
            ], 403);
        }
    }

    public function forceDelete($id)
    {
        $travelAgent = TravelAgent::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $travelAgent)) {
			$travelAgent->forceDelete();
			return response()->json(['status' => true]);
		}
		else {
			return response(['status' => 'You do not have permission to Permanent Delete Travel Agent ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $travelAgent = TravelAgent::withTrashed()->find($id);
            if(Auth::user()->can('restore', $travelAgent)) {
                $travelAgent->restore();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Restore Travel Agent ID: ".$id.""];
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
            $travelAgent = TravelAgent::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $travelAgent)) {
                $travelAgent->forceDelete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Permanent Delete Travel Agent ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors,
        ]);
	}
}
