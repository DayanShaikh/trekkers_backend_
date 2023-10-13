<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class LandingPageController extends Controller
{
	/**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(LandingPage::class, 'landing_page');
	 }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return LandingPage::query()
		->when($request->get('type_id') != '', function ($query) use($request){
			$query->where("type", $request->get('type_id'));
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
		->orderBy($request->order_by ?? 'type', $request->order ?? 'asc')
		->paginate($request->per_page ?? 25)
		;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' =>  [''],
            'month'  =>  [''],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $landingPage = LandingPage::create($data);
        return response()->json([
            'status'    =>  true,
            'landingPage' => $landingPage,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LandingPage  $landingPage
     * @return \Illuminate\Http\Response
     */
    public function show(LandingPage $landingPage)
    {
        return response()->json(['status' => true, 'landingPage' => $landingPage]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @param  \App\Models\LandingPage  $landingPage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LandingPage $landingPage)
    {
        $data = $request->validate([
            'type' =>  [''],
            'month'  =>  [''],
        ]);
        $landingPage->update($data);
        return response()->json([
            'status'    =>  true,
            'landingPage' => $landingPage,
        ]);
    }
	/**
     * Update the Lock Status for age group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LandingPage  $landingPage
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, LandingPage $landingPage)
    {
        $validated = $request->validate([
			'status' => ['required'],
		]);

		$landingPage->update(['status' => $request->boolean('status')]);
		$landingPage->save();

		return response()->json(['status' => true, 'message' => "Status has been updated"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LandingPage  $landingPage
     * @return \Illuminate\Http\Response
     */
    public function destroy(LandingPage $landingPage)
    {
        $landingPage->delete();
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
            $landingPage = LandingPage::find($id);
            if(Auth::user()->can('delete', $landingPage)) {
                $landingPage->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these landing page. ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $landingPage = LandingPage::withTrashed()->find($id);
		if(Auth::user()->can('restore', $landingPage)) {
			$landingPage->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this landing page. ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $landingPage = LandingPage::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $landingPage)) {
			$landingPage->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error'  => 'You do not have permission to permanent delete this landing page. ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $landingPage = LandingPage::withTrashed()->find($id);
            if(Auth::user()->can('restore', $landingPage)) {
                $landingPage->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these landing page. ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $landingPage = LandingPage::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $landingPage)) {
                $landingPage->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these landing page ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}

    public function getLandingPage(LandingPage $landingPage)
	{
        $page = $landingPage->page;
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

    public function updateLandingPage(Request $request, LandingPage $landingPage)
	{
        $data = $request->validate([
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
        $hasPage = $landingPage->page;
        if($hasPage){
            $landingPage->page()->update($data);
            if ($request->hasFile('image')){
                if(!empty($hasPage->image)){
                    Storage::delete($hasPage->image);
                }
                $hasPage->image = Storage::putFile('public/pages', $request->file('image'));
                $hasPage->save();
            }
        }
        else{
            $hasPage = $landingPage->page()->create($data);
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
