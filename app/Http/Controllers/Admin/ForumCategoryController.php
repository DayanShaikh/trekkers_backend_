<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ForumCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ForumCategoryController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(ForumCategory::class, 'forum_category');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return ForumCategory::query()
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })
        ->when($request->get("status") != null, function($query) use ($request){
            $query->where('status', $request->status );    
        })
        ->filter($request->only('search'))
			->orderBy($request->order_by ?? 'title', $request->order ?? 'desc')
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
			'title'   				=> ['string','required','unique:forum_categories']
        ]);
        $data['creater_id'] = auth()->user()->id;
        $forumCategory= ForumCategory::create($data);

        return response()->json([
            'status' => true,
            'forumCategory' => $forumCategory
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ForumCategory  $forumCategory
     * @return \Illuminate\Http\Response
     */
    public function show(ForumCategory $forumCategory)
    {
        return response()->json(['status' => true, 'forumCategory' => $forumCategory]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ForumCategory  $forumCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ForumCategory $forumCategory)
    {
        $data = $request->validate([
			'title'   				=> ['string','required', Rule::unique('forum_categories')->ignore($forumCategory)],
        ]);
        $forumCategory->update($data);

        return response()->json([
            'status' => true,
            'forumCategory' => $forumCategory
        ]);
    }

     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ForumCategory  $forumCategory
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, ForumCategory $forumCategory)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        $forumCategory->update(['status' => $request->boolean('status')]);
        $forumCategory->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ForumCategory  $forumCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(ForumCategory $forumCategory)
    {
        $forumCategory->delete();
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
            $forumCategory = ForumCategory::find($id);
            if(Auth::user()->can('delete', $forumCategory)) {
                $forumCategory->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these front categories ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $forumCategory = ForumCategory::withTrashed()->find($id);
		if(Auth::user()->can('restore', $forumCategory)) {
			$forumCategory->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this forum category ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $forumCategory = ForumCategory::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $forumCategory)) {
			$forumCategory->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete this forum category ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $forumCategory = ForumCategory::withTrashed()->find($id);
            if(Auth::user()->can('restore', $forumCategory)) {
                $forumCategory->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these front categories ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $forumCategory = ForumCategory::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $forumCategory)) {
                $forumCategory->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these front categories ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
