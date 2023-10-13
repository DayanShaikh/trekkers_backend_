<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TravelBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TravelBrandController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(TravelBrand::class, 'travel_brand');
	 }
     
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TravelBrand::with('travelAdmin')
        ->when('travelAdmin', function ($q) use($request){
            if($request->travel_admin_id) {
                $q->where('travel_admin_id', $request->travel_admin_id );
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
        ->orderBy($request->order_by ?? 'brand_name', $request->order ?? 'desc')
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
            'travel_admin_id' => ['int'],
            'brand_name' =>  ['string','required'],            
            'email'  =>  ['string','required','unique:travel_brands, email'],
            'password'  =>  ['string','required'],
            'commission_type'  =>  [''],
        ]);

        $data['creater_id'] =   auth()->user()->id;
        $travelBrand = TravelBrand::create($data);
        return response()->json([
            'status'    =>  true,
            'travelBrand' => $travelBrand,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TravelBrand  $travelBrand
     * @return \Illuminate\Http\Response
     */
    public function show(TravelBrand $travelBrand)
    {
        return response()->json(['status' => true, 'travelBrand' => $travelBrand]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TravelBrand  $travelBrand
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TravelBrand $travelBrand)
    {
        $data = $request->validate([
            'travel_admin_id' => ['int'],
            'brand_name' =>  ['string','required'],            
            'email'  =>  ['string','required',Rule::unique('travel_brands')->ignore($travelBrand->id)],
            'password'  =>  ['required'],
            'commission_type'  =>  [''],
            'status'  =>  [''],
        ]);
        $travelBrand->update($data);
        return response()->json([
            'status'    =>  true,
            'travelBrand' => $travelBrand,
        ]);
    }

     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TravelBrand  $travelBrand
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, TravelBrand $travelBrand)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        $travelBrand->update(['status' => $request->boolean('status')]);
        $travelBrand->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TravelBrand  $travelBrand
     * @return \Illuminate\Http\Response
     */
    public function destroy(TravelBrand $travelBrand)
    {
        $travelBrand->delete();
        return response()->json([
			'status' => true,
            'message' =>'Record has been deleted'
		]);
    }

    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $travelBrand = TravelBrand::find($id);
            if(Auth::user()->can('delete', $travelBrand)) {
                $travelBrand->delete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to delete User ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors
        ]);
    }

    public function restore(Request $request)
    {
        $travelBrand = TravelBrand::withTrashed()->find($id);
		if(Auth::user()->can('restore', $travelBrand)) {
			$travelBrand->restore();
			return response()->json(['status' => true]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore Travel Brand ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete(Request $request)
    {
        $travelBrand = TravelBrand::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $travelBrand)) {
			$travelBrand->forceDelete();
			return response()->json(['status' => true]);
		}
		else {
			return response(['status' => 'You do not have permission to Permanent Delete Travel Brand ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $travelBrand = TravelBrand::withTrashed()->find($id);
            if(Auth::user()->can('restore', $travelBrand)) {
                $travelBrand->restore();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Restore Travel Brand ID: ".$id.""];
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
            $travelBrand = TravelBrand::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $travelBrand)) {
                $travelBrand->forceDelete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Permanent Delete Travel Brand ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors,
        ]);
	}
}