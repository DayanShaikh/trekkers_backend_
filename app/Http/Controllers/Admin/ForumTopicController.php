<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ForumTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForumTopicController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(ForumTopic::class, 'forum_topic');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return ForumTopic::with('forumCategory', 'user')
        ->when('forum_category_id', function ($q) use($request){
            if($request->forum_category_id) {
                $q->where('forum_category_id', $request->forum_category_id );
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
            'forum_category_id' => ['int'],
            'user_id' => ['int'],
            'title' =>  ['string','required'],
            'content'   =>  ['string'],
            'announcement'  =>  ['int']
        ]);
        $data['creater_id'] = auth()->user()->id;
        $forumTopic = ForumTopic::create($data);
        return response()->json([
            'status'    =>  true,
            'forumTopic' => $forumTopic,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ForumTopic  $forumTopic
     * @return \Illuminate\Http\Response
     */
    public function show(ForumTopic $forumTopic)
    {
        $forumTopic = $forumTopic->with('forumCategory', 'user')->where('id', $forumTopic->id)->first();
        return response()->json(['status' => true, 'forumTopic' => $forumTopic]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ForumTopic  $forumTopic
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ForumTopic $forumTopic)
    {
        $data = $request->validate([
            'forum_category_id' => ['int'],
            'user_id' => ['int'],
            'title' =>  ['string','required'],
            'content'   =>  ['string'],
            'announcement'  =>  ['int']
        ]);
        $forumTopic->update($data);
        return response()->json([
            'status'    =>  true,
            'forumTopic' => $forumTopic,
        ]);
    }

    /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ForumTopic  $forumTopic
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, ForumTopic $forumTopic)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);
        $forumTopic->update(['status' => $request->boolean('status')]);
        $forumTopic->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ForumTopic  $forumTopic
     * @return \Illuminate\Http\Response
     */
    public function destroy(ForumTopic $forumTopic)
    {
        $forumTopic->delete();
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
            $forumTopic = ForumTopic::find($id);
            if(Auth::user()->can('delete', $forumTopic)) {
                $forumTopic->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these front topics ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $forumTopic = ForumTopic::withTrashed()->find($id);
		if(Auth::user()->can('restore', $forumTopic)) {
			$forumTopic->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this forum topic ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $forumTopic = ForumTopic::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $forumTopic)) {
			$forumTopic->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Selected record has been permanent deleted'
            ]);
		}
		else {
			return response(['status' => 'You do not have permission to permanent delete this forum topic ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $forumTopic = ForumTopic::withTrashed()->find($id);
            if(Auth::user()->can('restore', $forumTopic)) {
                $forumTopic->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these front topics ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $forumTopic = ForumTopic::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $forumTopic)) {
                $forumTopic->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these front topics ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
