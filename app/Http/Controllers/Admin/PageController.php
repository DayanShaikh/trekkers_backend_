<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\Page;
use App\Models\Trip;
use App\Models\AgeGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Storage;
use DB;

class PageController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(Page::class, 'page');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Page::query()->with('pageable')
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })
        ->when($request->get("status") != null, function($query) use ($request){
            $query->where('status', $request->status );    
        })
        ->when($request->get('pageable_type'), function ($query) use($request){
            if($request->get('pageable_type')=='nopage'){
                $query->where("pageable_type", null);
            }
            else{
                $query->where("pageable_type", $request->get('pageable_type'));
            }
		})
        ->filter($request->only('search'))
        ->orderBy($request->order_by ?? 'title', $request->order ?? 'desc')
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
            'pageable_type' => [''],
            'pageable_id' => [''],
            'page_name' => ['string'],
            'title' =>  ['string','required'],
            'content'   =>  [''],
            'highlights'    =>  [''],
            'meta_title'    =>  [''],
            'meta_description'    =>  [''],
            'meta_keywords'    =>  [''],
            'sitemap_title'    =>  [''],
            'sitemap_details'    =>  [''],
            'header_details'    =>  [''],
            'show_schema_markup'    =>  [''],
            'schema_title'    =>  [''],
			'show_search_box' => ['']
        ]);
        $data['creater_id'] = auth()->user()->id;
        $page = Page::create($data);
        if ($request->hasFile('image')){
			if(!empty($page->image)){
				Storage::delete($page->image);
			}
			$page->image = Storage::putFile('public/pages', $request->file('image'));
            $page->save();
		}
        return response()->json([
            'status'    =>  true,
            'page' => $page,
        ]);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function show(Page $page)
    {
        return response()->json(['status' => true, 'page' => $page]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'pageable_type' => [''],
            'pageable_id' => [''],
            'page_name' => ['string'],
            'title' =>  [''],
            'content'   =>  [''],
            'highlights'    =>  [''],
            'meta_title'    =>  [''],
            'meta_description'    =>  [''],
            'meta_keywords'    =>  [''],
            'sitemap_title'    =>  [''],
            'sitemap_details'    =>  [''],
            'header_details'    =>  [''],
            'show_schema_markup'    =>  [''],
            'schema_title'    =>  [''],
			'show_search_box' => ['']
        ]);
        $page->update($data);
        if ($request->hasFile('image')){
			if(!empty($page->image)){
				Storage::delete($page->image);
			}
			$page->image = Storage::putFile('public/pages', $request->file('image'));
            $page->save();
		}
        $page->save();

        return [
            'status' => true,
            'page' => $page
        ];
    }
    /**
	 * Update the Lock Status for page
	 *
	 * @param  \App\Models\Page  $page
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function updateActiveStatus(Request $request, Page $page)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);

        if(Auth::user()->can('update', $page)) {
            $page->update(['status' => $request->boolean('status')]);
            $page->save();
            return response()->json([
                'status' => true,
                'message' => "Status has been updated"
            ]);
        }
        else{
            return response()->json([
                'status' => false,
                'message' => "You do not have permission to update this status"
            ]);
        }
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function destroy(Page $page)
    {
        $page->delete();
        return response()->json([
			'status' => true,
            "message" => 'Record has been deleted',
		]);
    }

    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $page = Page::find($id);
            if(Auth::user()->can('delete', $page)) {
                $page->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these pages ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $page = Page::withTrashed()->find($id);
		if(Auth::user()->can('restore', $page)) {
			$page->restore();
			return response()->json([
                'status' => true,
                "message" => 'Selected record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this page ID: '.$id,
            ], 403);
		}
    }
    public function forceDelete($id)
    {
        $page = Page::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $page)) {
            if($page->image){
                Storage::delete($page->image);
            }
			$page->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete this page ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $page = Page::withTrashed()->find($id);
            if(Auth::user()->can('restore', $page)) {
                $page->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these pages ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $page = Page::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $page)) {
                if($page->image){
                    Storage::delete($page->image);
                }
                $page->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these pages ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}

    public function getPageModel(Request $request)
	{
		$data = '';
		if($request->pageable_type > 0){
			$data = $request->pageable_type::where('status', true)->get();
		}
        return $data;
    }

}
