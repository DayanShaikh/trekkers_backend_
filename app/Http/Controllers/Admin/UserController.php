<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Hash;
use Session;

class UserController extends Controller
{
	/**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(User::class, 'user');
	 }

	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return User::query()
            ->with('roles', 'userFields', 'locations')
            ->when($request->get("trash"), function($query) use ($request){
                if($request->get('trash')==1){
				    $query->onlyTrashed();
                }
			})
            ->when(($request->get("status") != null), function($query) use ($request){
                $query->where('status', $request->status );    
            })
			->when($request->get("role_ids"), function($query) use ($request){
				$query->whereHas('roles', function ($q) use($request){
					$q->whereIn('id', explode(',',$request->get("role_ids")));
				});
			})
            ->when($request->get("partner"), function($query) use ($request){
                if($request->get("partner")==1){
                    $query->whereHas('userFields', function ($q) use($request){
                        $q->where('field_key', 'is_partner')->where('field_value', '1');
                    });
                }
                elseif($request->get("partner")==2){
                    $query->whereHas('userFields', function ($q) use($request){
                        $q->where('field_key', 'is_partner')->where('field_value', '0');
                    });
                }
			})
			->filter($request->only('search'))
			->orderBy($request->order_by ?? 'name', $request->order ?? 'asc')
			->paginate($request->per_page ?? 25)
			;
    }

	public function getTourGuideUser(Request $request)
    {
        return User::select('users.*')
           	->whereHas('roles', function ($q) use($request){
				$q->where("name", "Tour Guide");
			})
            ->whereHas('userFields', function ($q) use($request){
				$q->where("field_key", "is_partner")
                ->where("field_value", "1");
			})
            ->when($request->get("trash"), function($query) use ($request){
                if($request->get('trash')==1){
				    $query->onlyTrashed();
                }
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            //'password' => ['required','string','min:8','regex:/[a-z]/','regex:/[A-Z]/','regex:/[@$!%*#?&]/'],
            'password' => ['required','string','min:8'],
            'roles' => ['array', 'min:1'],
        ]);
        $data['password'] = Hash::make($request->password);
		$user = User::create($data);
		$user->creater_id = auth()->user()->id;
		if (!empty($request->get('user_metas'))) {
			$data = [];
			foreach ($request->get('user_metas') as $key => $meta) {
				if (!empty($meta['value']))
					$data[] = ['key' => $meta['key'], 'value' => $meta['value']];
			}
			$user->userFields()->createMany($data);
		}
		$user->save();
        $user->roles()->sync($request->roles);
		$user = User::with('roles', 'userFields', 'locations')->where('id', $user->id)->first();
        return response()->json([
            'status'	=>  true,
            'user' 		=> $user,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
		$user = $user->with('userFields', 'locations')->where('id', $user->id)->first();
        return response()->json(['status' => true, 'user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($request->id)],
            'roles' => ['array', 'min:1']
        ]);

        if(isset($request->password)){
            $data['password'] = $request->validate([ 'password' => ['string', 'min:8']]);
            $data['password'] = Hash::make($request->password);
        }
        $user->update($data);
        $user->roles()->sync($request->get('roles'));
		if($request->get('location_id')){
			$user->locations()->sync($request->get('location_id'));
		}
		else{
			$user->locations()->delete();
		}
        $user->save();
		if (!empty($request->get('user_metas'))) {
			foreach ($request->user_metas as $meta) {
				//return $meta;

				// if (!empty($meta['value'])) {
					$doesExist = $user->userFields()
						->where('field_key', $meta['key'])
						->update(['field_value' => $meta['value']]);
					if (!$doesExist) {
						$user->userFields()->create(['field_key' => $meta['key'], 'field_value' => $meta['value']]);
					}
				// }
			}
			// $data = [];
			// foreach ($request->user_metas as $key => $meta) {
			// 	if (!empty($meta['value']))
			// 		$data[] = ['field_key' => $meta['key'], 'field_value' => $meta['value']];
			// }
			// $user->userFields()->createMany($data);
		}
		$user = User::with('roles', 'userFields', 'locations')->where('id', $user->id)->first();
        return response()->json([
            'status'	=>  true,
            'user'		=> $user,
        ]);
    }

	/**
	 * Update the Lock Status for user
	 *
	 * @param  \App\Models\User  $user
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function updateActiveStatus(Request $request, User $user)
	{
		$validated = $request->validate([
			'status' => ['required'],
		]);
        if(Auth::user()->can('update', $user)) {
            $user->update(['status' => $request->boolean('status')]);
            $user->save();
            return response()->json([
                'status' => true,
                'message'   =>  'Status has been updated'
            ]);
        }
        else{
            return response()->json([
                'status' => false,
                'message'   =>  'You do not have permission to update this status'
            ]);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        if($user->id !== 1)
        {
            $user->delete();
            return response()->json([
                'status' => true,
                "message" => 'Record has been deleted'
            ]);
        }
        else{
            return response()->json(['message' => 'You do not have permission to delete admin']);
        }
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
            $user = User::find($id);
            if(Auth::user()->can('delete', $user)) {
              $user->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these user ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
	}

    public function getAuthUser(Request $request)
	{
        $authUser = auth()->user();
        if($authUser){
            $authUser->fields = $authUser->userFields()->get();
        }
        if($request->has("website")){
            $user_id = null;
            if(in_array('view-myaccount', auth()->user()->given_permissions) || auth()->user()->id == 1){
                //return auth()->user()->id;
                //$user_id = Session::get('authUserId');
                $user_id = $request->session()->get('authUserId');
                //return $user_id;
                if($user_id){
                    $user = User::find($user_id);
                    $user->fields = $user->userFields()->get();
                    return response()->json([
                        "user" => $user
                    ]);
                }
            }
        }
		return response()->json([
			"user" => $authUser
		]);
	}

    public function restore($id)
    {
    	$user = User::withTrashed()->find($id);
		if(Auth::user()->can('restore', $user)) {
			$user->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this user ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $user = User::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $user)) {
			$user->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to permanent delete this user ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $user = User::withTrashed()->find($id);
            if(Auth::user()->can('restore', $user)) {
                $user->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these users ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $user = User::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $user)) {
                $user->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these users ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
