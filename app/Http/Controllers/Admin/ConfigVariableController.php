<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ConfigVariable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConfigVariableController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(ConfigVariable::class, 'config_variable');
	 }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return ConfigVariable::with('configPage')
        ->when('configPage', function ($q) use($request){
            if($request->config_page_id) {
                $q->where('config_page_id', $request->config_page_id );
            }
        })
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
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
            'config_page_id' => ['int'],
            'input_type' =>  [''],
            'name'   =>  ['required'],
            'notes'  =>  [''],
            'options'   =>  [''],
            'config_key'    =>  ['required'],
            'value' =>  [''],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $configVariable = ConfigVariable::create($data);         
        return response()->json([
            'status'    =>  true,
            'configVariable' => $configVariable,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ConfigVariable  $configVariable
     * @return \Illuminate\Http\Response
     */
    public function show(ConfigVariable $configVariable)
    {
        
        return response()->json(['status' => true, 'configVariable' => $configVariable]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ConfigVariable  $configVariable
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ConfigVariable $configVariable)
    {
        $data = $request->validate([
            'config_page_id' => ['int'],
            'input_type' =>  [''],
            'name'   =>  ['required'],
            'notes'  =>  [''],
            'options'   =>  [''],
            'config_key'    =>  ['required'],
            'value' =>  [''],
        ]);
        $configVariable->update($data);         
        return response()->json([
            'status'    =>  true,
            'configVariable' => $configVariable,
        ]);
    }
    /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ConfigVariable  $configVariable
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, ConfigVariable $configVariable)
	{
		$validated = $request->validate([
			'status' => ['required'],
		]);

		$configVariable->update(['status' => $request->boolean('status')]);
		$configVariable->save();

		return response()->json(['status' => true, 'message' => "Status has been updated"]);
	}
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ConfigVariable  $configVariable
     * @return \Illuminate\Http\Response
     */
    public function destroy(ConfigVariable $configVariable)
    {
        $configVariable->delete();
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
            $configVariable = ConfigVariable::find($id);
            if(Auth::user()->can('delete', $configVariable)) {
                $configVariable->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these config variable ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $configVariable = ConfigVariable::withTrashed()->find($id);
		if(Auth::user()->can('restore', $configVariable)) {
			$configVariable->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this config variable ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $configVariable = ConfigVariable::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $configVariable)) {
			$configVariable->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response(['status' => 'You do not have permission to permanent delete this config variable ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $configVariable = ConfigVariable::withTrashed()->find($id);
            if(Auth::user()->can('restore', $configVariable)) {
                $configVariable->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these config variable ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $configVariable = ConfigVariable::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $configVariable)) {
                $configVariable->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these config variables ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
