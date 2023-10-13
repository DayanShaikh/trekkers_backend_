<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogToCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(Blog::class, 'blog');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Blog::query()
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })
        ->when($request->get("status") != null, function($query) use ($request){
            $query->where('status', $request->status );    
        })
		->when($request->get("blog_category_id"), function($query) use ($request){
            $query->whereHas('blogCategories', fn ($q) => $q->where('blog_category_id', $request->get("blog_category_id")));
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
            'title' =>  ['required','string'],
            'seo_url'   =>  ['required','unique:blogs'],
            'excerpt'  =>  [''],
            'content'  =>  [''],
            'date'  =>  [''],
            'meta_title'  =>  [''],
            'meta_description'  =>  [''],
			'published_at'  =>  [''],
            'meta_keywords'  =>  [''],

        ]);
        $data['creater_id'] = auth()->user()->id;
        $blog = Blog::create($data);
        if($request->get('blog_categories')){
            $blog->blogCategories()->sync(explode(',', $request->get('blog_categories')));
        }
        if($request->get('related_blogs')){
            $blog->relatedBlogs()->sync(explode(',', $request->get('related_blogs')));
        }
        if ($request->hasFile('image')){
			if(!empty($blog->image)){
				Storage::delete($blog->image);
			}
			$blog->image = Storage::putFile('public/blogs', $request->file('image'));
            $blog->save();
		}
        return response()->json([
            'status'    =>  true,
            'blog' => $blog,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function show(Blog $blog)
    {
        $blog = $blog->with('blogCategories', 'relatedBlogs')->where('id', $blog->id)->first();
        return response()->json(['status' => true, 'blog' => $blog]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Blog $blog)
    {
        $data = $request->validate([
            'user_id' => ['int'],
            'title' =>  ['required','string'],
            'seo_url'   =>  ['required','string', Rule::unique('blogs')->ignore($blog)],
            'excerpt'  =>  [''],
            'content'  =>  [''],
            'date'  =>  [''],
            'meta_title'  =>  [''],
            'meta_description'  =>  [''],
			'published_at'  =>  [''],
            'meta_keywords'  =>  [''],
        ]);
        $blog->update($data);
        if($request->get('blog_categories')){
            $blog->blogCategories()->sync(explode(',', $request->get('blog_categories')));
        }
        if($request->get('related_blogs')){
            $blog->relatedBlogs()->sync(explode(',', $request->get('related_blogs')));
        }
        $blog->save();
        if ($request->hasFile('image')){
			if(!empty($blog->image)){
				Storage::delete($blog->image);
			}
			$blog->image = Storage::putFile('public/blogs', $request->file('image'));
            $blog->save();
		}
        return response()->json([
            'status'    =>  true,
            'blog' => $blog,
        ]);
    }

    /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, Blog $blog)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        $blog->update(['status' => $request->boolean('status')]);
        $blog->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function destroy(Blog $blog)
    {
        $blog->delete();
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
            $blog = Blog::find($id);
            if(Auth::user()->can('delete', $blog)) {
                $blog->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these blogs ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $blog = Blog::withTrashed()->find($id);
		if(Auth::user()->can('restore', $blog)) {
			$blog->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this blog ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $blog = Blog::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $blog)) {
            if(!empty($blog->image)){
                Storage::delete($blog->image);
            }
			$blog->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete this blog ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $blog = Blog::withTrashed()->find($id);
            if(Auth::user()->can('restore', $blog)) {
                $blog->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these blogs ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $blog = Blog::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $blog)) {
                if(!empty($blog->image)){
                    Storage::delete($blog->image);
                }
				$blog->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these blogs ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
