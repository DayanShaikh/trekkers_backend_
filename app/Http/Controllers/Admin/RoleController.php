<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{

    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->authorizeResource(Role::class, 'role');
	}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Role::select('roles.*')
            ->with('permissions')
            ->when($request->get("trash"), function($query) use ($request){
                if($request->get('trash')==1){
                    $query->onlyTrashed();
                }
			})
            ->when($request->get("status") != null, function($query) use ($request){
                $query->where('status', $request->status );    
            })
			->filter($request->only('search'))
			->orderBy($request->order_by ?? 'name', $request->order ?? 'asc')
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
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
        ]);

        $permissions = [];
		foreach ($request->permissions as $permission){
            foreach($permission["permissionAccess"] as $perm){
                if($perm["status"]==true){
			        $permissions[] = ['model' => $perm["model"], 'action' => $perm["action"]];
                }
            }
		}
        $role = Role::create($data);
		$role->creater_id = auth()->user()->id;
		$role->save();
        $role->permissions()->createMany($permissions);
        return response()->json([
            'status'    =>  true,
            'role' => $role,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        return response()->json(['status' => true, 'role' => $role]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
        ]);
        $role->permissions()->delete();
		$permissions = [];
		foreach ($request->permissions as $permission){
            foreach($permission["permissionAccess"] as $perm){
                if($perm["status"]==true){
			        $permissions[] = ['model' => $perm["model"], 'action' => $perm["action"]];
                }
            }
		}
		$role->permissions()->createMany($permissions);
        $role->update($data);
        $role->save();
        return response()->json([
            'status'    =>  true,
            'role' => $role,
        ]);
    }
    /**
	 * Update the Lock Status for role
	 *
	 * @param  \App\Models\Role  $role
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
    public function updateActiveStatus(Request $request, Role $role)
	{
		$validated = $request->validate([
			'status' => ['required'],
		]);

        if(Auth::user()->can('update', $role)) {
            $role->update(['status' => $request->boolean('status')]);
            $role->save();
            return response()->json(['status' => true, 'message' => "Role status has been updated"]);
        }
        else{
            return response()->json(['status' => false, 'message' => "You do not have permission to update this status"]);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Role  $review
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {

        $role->delete();
        return response()->json([
            'status' => true,
            "message" => 'Record has been deleted'
        ]);
    }

    /**
	 * Remove the multiple resource from storage.
	 *
	 * @param  int  $ids[]
	 * @return \Illuminate\Http\Response
	 */
	public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $role = Role::find($id);
            if(Auth::user()->can('delete',$role)) {
                $role->delete();
                $count++;}
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these roles ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
	}

    public function restore($id)
    {
        $role = Role::withTrashed()->find($id);
        if(Auth::user()->can('restore', $role)){
            $role->restore();
            return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
        }
        else{
            return response()->json([
                'status' => false,
                'error' => 'You do not have permission to restore this role ID: '.$id,
            ], 403);
        }
    }

    public function forceDelete($id)
    {
        $role = Role::withTrashed()->find($id);
        if(Auth::user()->can('forceDelete', $role)){
           $role->forceDelete();
            return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
        }
        else{
            return response()->json([
                'status' => false,
                'error' => 'You do not have permission to permanent delete this role ID: '.$id,
            ], 403);
        }
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            // $role = Role::withTrashed($id);
            $role = Role::withTrashed()->find($id);
            if(Auth::user()->can('restore',$role)) {
                $role->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these roles ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
		$count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $role = Role::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $role)) {
                $role->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent these roles ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
