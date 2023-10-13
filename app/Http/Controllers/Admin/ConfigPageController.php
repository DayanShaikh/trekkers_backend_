<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ConfigPage;
use App\Models\ConfigVariable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConfigPageController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(ConfigPage::class, 'config_page');
	 }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return ConfigPage::query()
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })
        ->filter($request->only('search'))
        ->orderBy($request->order_by ?? 'sortorder', $request->order ?? 'desc')
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
            'title' =>  ['required','string'],
            'show_in_menu'   =>  [''],
            'sortorder'  =>  [''],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $configPage = ConfigPage::create($data);
        return response()->json([
            'status'    =>  true,
            'configPage' => $configPage,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ConfigPage  $configPage
     * @return \Illuminate\Http\Response
     */
    public function show(ConfigPage $configPage)
    {
        return response()->json(['status' => true, 'configPage' => $configPage]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ConfigPage  $configPage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ConfigPage $configPage)
    {
        $data = $request->validate([
            'title' =>  ['required','string'],
            'show_in_menu'   =>  [''],
            'sortorder'  =>  [''],
        ]);
        $configPage->update($data);
        return response()->json([
            'status'    =>  true,
            'configPage' => $configPage,
        ]);
    }

	/**
	 * Update the Lock Status for configPage
	 *
	 * @param  \App\Models\ConfigPage  $configPage
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function updateActiveStatus(Request $request, ConfigPage $configPage)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);

        $configPage->update(['status' => $request->boolean('status')]);
        $configPage->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ConfigPage  $configPage
     * @return \Illuminate\Http\Response
     */
    public function destroy(ConfigPage $configPage)
    {
        $configPage->delete();
        return response()->json([
			'status' => true,
            "message"   =>  "Record has been deleted",
		]);
    }
    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $configPage = ConfigPage::find($id);
            if(Auth::user()->can('delete', $configPage)) {
                $configPage->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to Delete Config Variable ID: [ ". implode(",",$errors)." ]": "",
            "message"   =>  "Selected record has been deleted",
        ]);
    }

    public function variables($id)
    {
        return $configVariables = ConfigVariable::with('config_page')
        ->where('config_page_id',$id)
        ->get();
    }
    public function getVariables(Request $request, ConfigPage $configPage)
    {
        // return $configPage;
        $variables = ConfigVariable::where('config_page_id', $configPage->id)->orderBy('id')->get();
        return response()->json(['status' => true, 'configPage' => $variables]);
    }

    public function saveVariables(Request $request, ConfigPage $configPage)
    {
        // $configPage = ConfigPage::find($id);
        $values = $request->get('config_variable');
        $files = $request->file("config_variable");
        $delete_files = $request->get("delete_files");
        foreach($configPage->configVariables as $configVariable) {
            if($configVariable->input_type != 5 ) {
                if (isset($values[$configVariable->id])) {
                    $configVariable->value = is_array($values[$configVariable->id]) ? implode(",", $values[$configVariable->id]) : $values[$configVariable->id];
                } else {
                    $configVariable->value = '';
                }
                $configVariable->save();
            }
            else{
				if ($files && $files[$configVariable->id]){
					if(!empty($files[$configVariable->id])){
						Storage::delete($files[$configVariable->id]);
					}
					$configVariable->value = Storage::putFile('public/config/', $files[$configVariable->id]);
					$configVariable->save();
				}
                /*if(isset($files[$configVariable->id]) || isset($delete_files[$configVariable->id])){
                     if(!empty($configVariable->value)) {
                         $file_name = $configPage->getDir() . "/" . $configVariable->value;
                         if(file_exists($file_name)) {
                            unlink($file_name);
                        }
                         $configVariable->value = '';
                         $configVariable->save();
                     }
                 }
                 if(isset($files[$configVariable->id])){
                     $file_name = $files[$configVariable->id]->getClientOriginalName();
                     $files[$configVariable->id]->move($configPage->getDir(), $file_name);
                     $configVariable->value = $file_name;
                     $configVariable->save();
                 }*/
            }
        }
        return response()->json([
            'status' => true,
        ]);
    }

    public function restore($id)
    {
        $configPage = ConfigPage::withTrashed()->find($id);
		if(Auth::user()->can('restore', $ConfigPage)) {
			$ConfigPage->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this config page ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $ConfigPage = ConfigPage::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $ConfigPage)) {
			$ConfigPage->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response(['status' => 'You do not have permission to permanent delete this config page ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $configPage = ConfigPage::withTrashed()->find($id);
            if(Auth::user()->can('restore', $configPage)) {
                $configPage->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these config pages ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $configPage = ConfigPage::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $configPage)) {
                $configPage->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these config pages ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
