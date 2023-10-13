<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class BlogCategoryController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(BlogCategory::class, 'blog_category');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return BlogCategory::query()
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
            'title' => ['string','required',],
            'seo_url' =>  ['string','unique:blog_categories'],
            'meta_title'   =>  [''],
            'meta_description'  =>  [''],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $blogCategory = BlogCategory::create($data);       
        return response()->json([
            'status'    =>  true,
            'blogCategory' => $blogCategory,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BlogCategory  $blogCategory
     * @return \Illuminate\Http\Response
     */
    public function show(BlogCategory $blogCategory)
    {
        return response()->json(['status' => true, 'blogCategory' => $blogCategory]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BlogCategory  $blogCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BlogCategory $blogCategory)
    {
        $data = $request->validate([
            'title' => ['string','required'],
            'seo_url' =>  ['string', Rule::unique('blog_categories')->ignore($blogCategory)],
            'meta_title'   =>  [''],
            'meta_description'  =>  [''],
        ]);
        $blogCategory->update($data);
        return response()->json([
            'status'    =>  true,
            'blogCategory' => $blogCategory,
        ]);
    }

     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BlogCategory  $blogCategory
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, BlogCategory $blogCategory)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
       
        $blogCategory->update(['status' => $request->boolean('status')]);
        $blogCategory->save();
        return response()->json([
           'status' => true,
           'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BlogCategory  $blogCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(BlogCategory $blogCategory)
    {
        $blogCategory->delete();
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
            $blogCategory = BlogCategory::find($id);
            if(Auth::user()->can('delete', $blogCategory)) {
                $blogCategory->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these blog category ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
	}

    public function restore($id)
    {
        $blogCategory = BlogCategory::withTrashed()->find($id);
		if(Auth::user()->can('restore', $blogCategory)) {
			$blogCategory->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this blog category ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $BlogCategory = BlogCategory::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $BlogCategory)) {
			$BlogCategory->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete this blog category ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $blogCategory = BlogCategory::withTrashed()->find($id);
            if(Auth::user()->can('restore', $blogCategory)) {
                $blogCategory->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these blog categories ID: [ ". implode(",",$errors)." ]": "","errors" => $errors,
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{        
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $blogCategory = BlogCategory::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $blogCategory)) {
                $blogCategory->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these blog category ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
