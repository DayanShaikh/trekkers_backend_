<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SupportArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SupportArticleController extends Controller
{
     /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(SupportArticle::class, 'support_article');
	 }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return SupportArticle::with('supportCategory', 'page')
        ->when('support_category_id', function ($q) use($request){
            if($request->support_category_id) {
                $q->where('support_category_id', $request->support_category_id );
            }
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
            'support_category_id' => ['int'],
            'title' =>  ['required'],
            'excerpt'  =>  [''],
            'time'  =>  [''],
            'date'  =>  [''],
            'sortorder'  =>  [''],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $supportArticle = SupportArticle::create($data);
        return response()->json([
            'status'    =>  true,
            'supportArticle' => $supportArticle,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SupportArticle  $supportArticle
     * @return \Illuminate\Http\Response
     */
    public function show(SupportArticle $supportArticle)
    {
		$supportArticle = $supportArticle->with('supportCategory')->where('id', $supportArticle->id)->first();
        return response()->json(['status' => true, 'supportArticle' => $supportArticle]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SupportArticle  $supportArticle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SupportArticle $supportArticle)
    {
        $data = $request->validate([
            'support_category_id' => ['int'],
            'title' =>  ['required'],
            'excerpt'  =>  [''],
            'time'  =>  [''],
            'date'  =>  [''],
            'sortorder'  =>  [''],
        ]);
        $supportArticle->update($data);
        return response()->json([
            'status'    =>  true,
            'supportArticle' => $supportArticle,
        ]);
    }

     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SupportArticle  $supportArticle
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, SupportArticle $supportArticle)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        $supportArticle->update(['status' => $request->boolean('status')]);
        $supportArticle->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SupportArticle  $supportArticle
     * @return \Illuminate\Http\Response
     */
    public function destroy(SupportArticle $supportArticle)
    {
        $supportArticle->delete();
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
            $supportArticle = SupportArticle::find($id);
            if(Auth::user()->can('delete', $supportArticle)) {
                $supportArticle->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these support articles ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $supportArticle = SupportArticle::withTrashed()->find($id);
		if(Auth::user()->can('restore', $supportArticle)) {
			$supportArticle->restore();
			return response()->json([
                'status' => true,
                'message'   =>  'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore support article ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $supportArticle = SupportArticle::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $supportArticle)) {
			$supportArticle->forceDelete();
			return response()->json([
                'status' => true,
                'message'   =>  'Record has been permanent deleted']);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete this support article ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $supportArticle = SupportArticle::withTrashed()->find($id);
            if(Auth::user()->can('restore', $supportArticle)) {
                $supportArticle->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restored these support articles ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $supportArticle = SupportArticle::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $supportArticle)) {
                $supportArticle->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these support articles ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}

	public function getSupportArticlePage(SupportArticle $supportArticle)
	{
        $page = $supportArticle->page;
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

    public function updateSupportArticlePage(Request $request, SupportArticle $supportArticle)
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
        $hasPage = $supportArticle->page;
        if($hasPage){
            $supportArticle->page()->update($data);
            if ($request->hasFile('image')){
                if(!empty($hasPage->image)){
                    Storage::delete($hasPage->image);
                }
                $hasPage->image = Storage::putFile('public/pages', $request->file('image'));
                $hasPage->save();
            }
        }
        else{
            $hasPage = $supportArticle->page()->create($data);
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
