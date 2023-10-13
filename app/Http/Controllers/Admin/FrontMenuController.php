<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\FrontMenu;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class FrontMenuController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(FrontMenu::class, 'front_menu');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return FrontMenu::query()->with('frontMenuParent')
        ->when($request->get('position_id') != '', function ($query) use($request){
			$query->where("position", $request->get('position_id'));
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
        ->orderBy($request->order_by ?? 'title', $request->order ?? 'asc')
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
            'position' => ['int', 'required'],
            'title' =>  ['required','string'],
            'url'   =>  ['required', 'string'],
            'parent_id'  =>  ['int'],
            'sortorder'  =>  ['int', 'required'],
            'is_default'  =>  ['']
        ]);
        $data['creater_id'] = auth()->user()->id;
        $frontMenu = FrontMenu::create($data);
        return response()->json([
            'status'    =>  true,
            'frontMenu' => $frontMenu,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FrontMenu  $frontMenu
     * @return \Illuminate\Http\Response
     */
    public function show(FrontMenu $frontMenu)
    {
		$frontMenu = $frontMenu->with('frontMenuParent')->where('id', $frontMenu->id)->first();
        return  response()->json(['status' => true, 'frontMenu' => $frontMenu]);
    }
	
	public function frontMenuParent(Request $request)
    {
		$frontMenu = FrontMenu::with('frontSubMenu')->when($request->get('search'), function ($query) use($request){
			$query->where('title', 'like', '%'.$request->search.'%');
		})->orderBy('sortorder')->get();
        return  response()->json(['status' => true, 'frontMenu' => $frontMenu]);
	}

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FrontMenu  $frontMenu
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FrontMenu $frontMenu)
    {
        $data = $request->validate([
            'position' => ['int', 'required'],
            'title' =>  ['required','string'],
            'url'   =>  ['required','string'],
            'parent_id'  =>  ['int'],
            'sortorder'  =>  ['int'],
            'is_default'  =>  ['']
        ]);
        $frontMenu->update($data);
        return response()->json([
            'status'    =>  true,
            'frontMenu' => $frontMenu,
        ]);
    }
    /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FrontMenu  $frontMenu
     * @return \Illuminate\Http\Response
     */
    public function updateActiveDefault(Request $request, FrontMenu $frontMenu)
	{
        $validated = $request->validate([
			'is_default' => ['required'],
		]);
        if(Auth::user()->can('update', $frontMenu)) {
            $frontMenu->update(['is_default' => $request->boolean('is_default')]);
            $frontMenu->save();
            return response()->json([
                'status' => true,
                'message'   =>  'Default Status has been updated'
            ]);
        }
        else{
            return response()->json([
                'status' => false,
                'message'   => 'You do not have permission to update this default status'
            ]);
        }
	}
    /**
     * Update the Lock Default.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FrontMenu  $frontMenu
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, FrontMenu $frontMenu)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        if(Auth::user()->can('update', $frontMenu)) {
            $frontMenu->update(['status' => $request->boolean('status')]);
            $frontMenu->save();
            return response()->json([
                'status' => true,
                'message'   =>  'Status has been updated'
            ]);
        }
        else{
            return response()->json([
                'status' => false,
                'message'   => 'You do not have permission to update this status'
            ]);
        }
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FrontMenu  $frontMenu
     * @return \Illuminate\Http\Response
     */
    public function destroy(FrontMenu $frontMenu)
    {
        $frontMenu->delete();
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
            $frontMenu = FrontMenu::find($id);
            if(Auth::user()->can('delete', $frontMenu)) {
                $frontMenu->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these front menus ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
	}

    public function restore($id)
    {
        $frontMenu = FrontMenu::withTrashed()->find($id);
		if(Auth::user()->can('restore', $frontMenu)) {
			$frontMenu->restore();
			return response()->json([
                'status' => 'true',
                'message'  => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this front menu ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $frontMenu = FrontMenu::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $frontMenu)) {
			$frontMenu->forceDelete();
			return response()->json([
                'status' => true,
                'message' =>  'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to permanent delete this front menu ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $frontMenu = FrontMenu::withTrashed()->find($id);
            if(Auth::user()->can('restore', $frontMenu)) {
                $frontMenu->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these front menus ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}
    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $frontMenu = FrontMenu::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $frontMenu)) {
                $frontMenu->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these front menus ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
