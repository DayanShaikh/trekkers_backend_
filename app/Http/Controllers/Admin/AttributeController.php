<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class AttributeController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(Attribute::class, 'attribute');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Attribute::select('attributes.*')
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
            'title' => ['string','required','unique:attributes'],
            'seo_url_path'   =>  [''],
			'travel_insurance_fees'   =>  [''],
			'is_survival_adventure_insurance_active'   =>  [''],
			'sortorder'   =>  [''],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $attribute = Attribute::create($data);
        $attribute->save();
        return response()->json([
            'status'    =>  true,
            'attribute' => $attribute,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\Response
     */
    public function show(Attribute $attribute)
    {
        return response()->json(['status' => true, 'attribute' => $attribute]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Attribute $attribute)
    {
        $data = $request->validate([
            'title' => ['string','required', Rule::unique('attributes')->ignore($attribute)],
            'seo_url_path'   =>  [''],
			'travel_insurance_fees'   =>  [''],
			'is_survival_adventure_insurance_active'   =>  [''],
			'sortorder'   =>  [''],
        ]);
        $attribute->update($data);
        $attribute->save();
        return response()->json([
            'status'    =>  true,
            'attribute' => $attribute,
        ]);
    }

     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, Attribute $attribute)
	{
		$validated = $request->validate([
			'status' => ['required'],
		]);

		$attribute->update(['status' => $request->boolean('status')]);
		$attribute->save();

		return response()->json(['status' => true, 'message' => "Status has been updated"]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Attribute  $attribute
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attribute $attribute)
    {
        $attribute->delete();
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
            $attribute = Attribute::find($id);
            if(Auth::user()->can('delete', $attribute)) {
                $attribute->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these attribute ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
	}

    public function restore($id)
    {
        $attribute = Attribute::withTrashed()->find($id);
		if(Auth::user()->can('restore', $attribute)) {
			$attribute->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this attribute ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $attribute = Attribute::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $attribute)) {
			$attribute->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete this attribute ID: '.$id], 403);
		}
    }
    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $attribute = Attribute::withTrashed()->find($id);
            if(Auth::user()->can('restore', $attribute)) {
                $attribute->restore();
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
            $attribute = Attribute::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $attribute)) {
                $attribute->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanant delete these attributes ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
