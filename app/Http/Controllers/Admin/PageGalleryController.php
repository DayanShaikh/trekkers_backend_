<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PageGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PageGalleryController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(PageGallery::class, 'page_gallery');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return PageGallery::with('page')
        ->when('page', function ($q) use($request){
            if($request->page_id) {
                $q->where('page_id', $request->page_id );
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
        ->orderBy($request->order_by ?? 'sortorder', $request->order ?? 'asc')
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
            'page_id' => ['required','int'],
            'title' =>  ['required'],
            'image'   =>  [''],
            'sortorder'   =>  [''],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $pageGallery = PageGallery::create($data);
        if ($request->hasFile('image')){
			if(!empty($pageGallery->image)){
				Storage::delete($pageGallery->image);
			}
			$pageGallery->image = Storage::putFile('public/page-galleries', $request->file('image'));
            $pageGallery->save();
        }
        return response()->json([
            'status'    =>  true,
            'pageGallery' => $pageGallery,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PageGallery  $pageGallery
     * @return \Illuminate\Http\Response
     */
    public function show(PageGallery $pageGallery)
    {
        return response()->json(['status' => true, 'pageGallery' => $pageGallery]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PageGallery  $pageGallery
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PageGallery $pageGallery)
    {
        $data = $request->validate([
            'page_id' => ['required','int'],
            'title' =>  ['required'],
            'image'   =>  [''],
            'sortorder'   =>  [''],
        ]);
        $pageGallery->update($data);
        if ($request->hasFile('image')){
			if(!empty($pageGallery->image)){
				Storage::delete($pageGallery->image);
			}
			$pageGallery->image = Storage::putFile('public/page-galleries', $request->file('image'));
            $pageGallery->save();
        }
        return response()->json([
            'status'    =>  true,
            'pageGallery' => $pageGallery,
        ]);
    }

        /**
     * Update the Lock Status for page gallery.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PageGallery  $pageGallery
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, PageGallery $pageGallery)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        $pageGallery->update(['status' => $request->boolean('status')]);
        $pageGallery->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PageGallery  $pageGallery
     * @return \Illuminate\Http\Response
     */
    public function destroy(PageGallery $pageGallery)
    {
        $pageGallery->delete();
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
            $pageGallery = PageGallery::find($id);
            if(Auth::user()->can('delete', $pageGallery)) {
                $pageGallery->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these page galleries ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function GetDataByPageId($id)
    {
        return PageGallery::with('page')
        ->where('page_id',$id)
        ->get();
    }

    public function restore($id)
    {
        $pageGallery = PageGallery::withTrashed()->find($id);
		if(Auth::user()->can('restore', $pageGallery)) {
			$pageGallery->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this page gallery ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $pageGallery = PageGallery::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $pageGallery)) {
			if(!empty($pageGallery->image)){
                Storage::delete($pageGallery->image);
            }
			$pageGallery->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete this page gallery ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $pageGallery = PageGallery::withTrashed()->find($id);
            if(Auth::user()->can('restore', $pageGallery)) {
                $pageGallery->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these page galleries ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $pageGallery = PageGallery::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $pageGallery)) {
                if(!empty($pageGallery->image)){
                    Storage::delete($pageGallery->image);
                }
				$pageGallery->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these page galleries ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
