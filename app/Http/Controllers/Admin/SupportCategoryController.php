<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SupportCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class SupportCategoryController extends Controller
{
     /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(SupportCategory::class, 'support_category');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return SupportCategory::query()
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
            'title' => ['required'],
            'sortorder'  =>  [''],
        ]);
        $data['craeter_id'] = auth()->user()->id;
        $supportCategory = SupportCategory::create($data);
        if ($request->hasFile('icon')){
			if(!empty($supportCategory->icon)){
				Storage::delete($supportCategory->icon);
			}
			$supportCategory->icon = Storage::putFile('public/support-categories', $request->file('icon'));
            $supportCategory->save();
		}
        return response()->json([
            'status'    =>  true,
            'supportCategory' => $supportCategory,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SupportCategory  $supportCategory
     * @return \Illuminate\Http\Response
     */
    public function show(SupportCategory $supportCategory)
    {
        return response()->json(['status' => true, 'supportCategory' => $supportCategory]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SupportCategory  $supportCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SupportCategory $supportCategory)
    {
        $data = $request->validate([
            'title' => ['required'],
            'sortorder'  =>  [''],
        ]);
        $supportCategory->update($data);
        if ($request->hasFile('icon')){
			if(!empty($supportCategory->icon)){
				Storage::delete($supportCategory->icon);
			}
			$supportCategory->icon = Storage::putFile('public/support-categories', $request->file('icon'));
            $supportCategory->save();
		}
        return response()->json([
            'status'    =>  true,
            'supportCategory' => $supportCategory,
        ]);
    }

     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SupportCategory  $supportCategory
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, SupportCategory $supportCategory)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);

        $supportCategory->update(['status' => $request->boolean('status')]);
        $supportCategory->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SupportCategory  $supportCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(SupportCategory $supportCategory)
    {
        $supportCategory->delete();
        return response()->json([
			'status' => true,
            'message'   =>  'Record has been deleted'
		]);
    }

 public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $supportCategory = SupportCategory::find($id);
            if(Auth::user()->can('delete', $supportCategory)) {
                $supportCategory->delete();
                $count++;
            }
            else{
                $errors[] = $id;;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these support categories ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }


    public function restore($id)
    {
        $supportCategory = SupportCategory::withTrashed()->find($id);
		if(Auth::user()->can('restore', $supportCategory)) {
			$supportCategory->restore();
			return response()->json([
                'status' => true,
                'message' =>  'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this support category ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $supportCategory = SupportCategory::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $supportCategory)) {
            if(!empty($supportCategory->icon)){
                Storage::delete($supportCategory->icon);
            }
			$supportCategory->forceDelete();
			return response()->json([
                'status' => true,
                'message'   =>  'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete support category ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $supportCategory = SupportCategory::withTrashed()->find($id);
            if(Auth::user()->can('restore', $supportCategory)) {
                $supportCategory->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to retore these support categories ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $supportCategory = SupportCategory::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $supportCategory)) {
                if(!empty($supportCategory->icon)){
                    Storage::delete($supportCategory->icon);
                }
				$supportCategory->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these support categories ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}

	public function getSupportCategoryPage(SupportCategory $supportCategory)
	{
        $page = $supportCategory->page;
        if($page){
            return response()->json([
				"status" => true,
                'page' => $page,
            ]);
        }
        return response()->json([
            "status" => false,
            "message" => 'Page not found'
        ]);
    }

    public function updateSupportCategoryPage(Request $request, SupportCategory $supportCategory)
	{
        $data = $request->validate([
            'page_name' => ['string'],
            'title' =>  [''],
            'content'   =>  ['required'],
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
        $hasPage = $supportCategory->page;
        if($hasPage){
            $supportCategory->page()->update($data);
            if ($request->hasFile('image')){
                if(!empty($hasPage->image)){
                    Storage::delete($hasPage->image);
                }
                $hasPage->image = Storage::putFile('public/pages', $request->file('image'));
                $hasPage->save();
            }
        }
        else{
            $hasPage = $supportCategory->page()->create($data);
            if ($request->hasFile('image')){
                if(!empty($hasPage->image)){
                    Storage::delete($hasPage->image);
                }
                $hasPage->image = Storage::putFile('public/pages', $request->file('image'));
                $hasPage->save();
            }
        }
        return response()->json([
            'status' => true,
            'page' => $hasPage,
        ]);
    }
}
