<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\HeaderVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class HeaderVideoController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(HeaderVideo::class, 'header_video');
	 }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return HeaderVideo::query()
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
            'title' => ['string','required'],
        ]);

        $data['creater_id'] = auth()->user()->id;
        $headerVideo = HeaderVideo::create($data);     
        if ($request->hasFile('webm_format')){
			if(!empty($headerVideo->webm_format)){
				Storage::delete($headerVideo->webm_format);
			}
			$headerVideo->webm_format = Storage::putFile('public/header_videos/webm_formats', $request->file('webm_format'));
		}  
        if ($request->hasFile('mp4_format')){
			if(!empty($headerVideo->mp4_format)){
				Storage::delete($headerVideo->mp4_format);
			}
			$headerVideo->mp4_format = Storage::putFile('public/header_videos/mp4_formats', $request->file('mp4_format'));
		}
        $headerVideo->save();
        return response()->json([
            'status'    =>  true,
            'headerVideo' => $headerVideo,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HeaderVideo  $headerVideo
     * @return \Illuminate\Http\Response
     */
    public function show(HeaderVideo $headerVideo)
    {
        return response()->json(['status' => true, 'headerVideo' => $headerVideo]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HeaderVideo  $headerVideo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, HeaderVideo $headerVideo)
    {
        $data = $request->validate([
            'title' => ['string','required'],
        ]);

        $headerVideo->update($data);     
        if ($request->hasFile('webm_format')){
			if(!empty($headerVideo->webm_format)){
				Storage::delete($headerVideo->webm_format);
			}
			$headerVideo->webm_format = Storage::putFile('public/header_videos/webm_formats', $request->file('webm_format'));
            $headerVideo->save();
		}  
        if ($request->hasFile('mp4_format')){
			if(!empty($headerVideo->mp4_format)){
				Storage::delete($headerVideo->mp4_format);
			}
			$headerVideo->mp4_format = Storage::putFile('public/header_videos/mp4_formats', $request->file('mp4_format'));
            $headerVideo->save();
		}  
        
        return response()->json([
            'status'    =>  true,
            'headerVideo' => $headerVideo,
        ]);
    }

     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HeaderVideo  $headerVideo
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, HeaderVideo $headerVideo)
	{
		$validated = $request->validate([
			'status' => ['required'],
		]);

		$headerVideo->update(['status' => $request->boolean('status')]);
		$headerVideo->save();

		return response()->json(['status' => true, 'message' => "Status has been updated"]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HeaderVideo  $headerVideo
     * @return \Illuminate\Http\Response
     */
    public function destroy(HeaderVideo $headerVideo)
    {
        $headerVideo->delete();
        return response()->json([
            'status'=>true,
            "message" => 'Record has been deleted'
        ]);
    }

    public function massDestroy(Request $request)
    {
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $headerVideo = HeaderVideo::find($id);
            if(Auth::user()->can('delete', $headerVideo)) {
                $headerVideo->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these header videos ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);        
    }

    public function restore($id)
    {
        $headerVideo = HeaderVideo::withTrashed()->find($id);
		if(Auth::user()->can('restore', $headerVideo)) {
			$headerVideo->restore();
			return response()->json([
                'status' => true,
                "message" => 'Selected record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this header video ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $headerVideo = HeaderVideo::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $headerVideo)) {
			$headerVideo->forceDelete();
            if(!empty($headerVideo->webm_format)){
                Storage::delete($video->webm_format);
            }
            if(!empty($headerVideo->mp4_format)){
                Storage::delete($video->mp4_format);
            }
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete this header video ID: '.$id], 403);
		}       
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $headerVideo = HeaderVideo::withTrashed()->find($id);
            if(Auth::user()->can('restore', $headerVideo)) {
                $headerVideo->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these header videos ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $headerVideo = HeaderVideo::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $headerVideo)) {
                $headerVideo->forceDelete();
                if(!empty($headerVideo->webm_format)){
                    Storage::delete($video->webm_format);
                }
                if(!empty($headerVideo->mp4_format)){
                    Storage::delete($video->mp4_format);
                }
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these header videos ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
