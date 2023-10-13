<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AgeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AgeGroupController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(AgeGroup::class, 'age_group');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return AgeGroup::query()->with('trips','trips.location')
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
            'title' =>  ['required','string','unique:age_groups'],
            'sortorder'  =>  [''],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $ageGroup = AgeGroup::create($data);
        return response()->json([
            'status'    =>  true,
            'ageGroup' => $ageGroup,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AgeGroup  $ageGroup
     * @return \Illuminate\Http\Response
     */
    public function show(AgeGroup $ageGroup)
    {
        return response()->json(['status' => true, 'ageGroup' => $ageGroup]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AgeGroup  $ageGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AgeGroup $ageGroup)
    {
        $data = $request->validate([
            'title' =>  ['required','string',Rule::unique('age_groups')->ignore($ageGroup)],
            'sortorder'  =>  [''],
        ]);
        $ageGroup->update($data);
        return response()->json([
            'status'    =>  true,
            'ageGroup' => $ageGroup,
        ]);
    }

     /**
     * Update the Lock Status for age group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AgeGroup  $ageGroup
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, AgeGroup $ageGroup)
    {
        $validated = $request->validate([
			'status' => ['required'],
		]);

		$ageGroup->update(['status' => $request->boolean('status')]);
		$ageGroup->save();

		return response()->json(['status' => true, 'message' => "Status has been updated"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AgeGroup  $ageGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(AgeGroup $ageGroup)
    {
        $ageGroup->delete();
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
            $ageGroup = AgeGroup::find($id);
            if(Auth::user()->can('delete', $ageGroup)) {
                $ageGroup->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these age groups. ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $ageGroup = AgeGroup::withTrashed()->find($id);
		if(Auth::user()->can('restore', $ageGroup)) {
			$ageGroup->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this age group. ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $ageGroup = AgeGroup::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $ageGroup)) {
			$ageGroup->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error'  => 'You do not have permission to permanent delete this age group. ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $ageGroup = AgeGroup::withTrashed()->find($id);
            if(Auth::user()->can('restore', $ageGroup)) {
                $ageGroup->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these age group. ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $ageGroup = AgeGroup::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $ageGroup)) {
                $ageGroup->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these age groups ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
