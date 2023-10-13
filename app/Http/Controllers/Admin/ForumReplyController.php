<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ForumReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForumReplyController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(ForumReply::class, 'forum_reply');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return ForumReply::with('forumTopic', 'user')
        ->when('forum_topic_id', function ($q) use($request){
            if($request->forum_topic_id) {
                $q->where('forum_topic_id', $request->forum_topic_id );
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
            'forum_topic_id'     =>  ['int','required'],
            'user_id'     =>  ['required'],
            'content'   =>  ['string'],
           ]);
           $data['creater_id'] = auth()->user()->id;
           $forumReply = ForumReply::create($data);
           return response()->json([
            'status'    =>  true,
            'forumReply' => $forumReply,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ForumReply  $forumReply
     * @return \Illuminate\Http\Response
     */
    public function show(ForumReply $forumReply)
    {
        $forumReply = $forumReply->with('forumTopic', 'user')->where('id', $forumReply->id)->first();
        return response()->json(['status' => true, 'forumReply' => $forumReply]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ForumReply  $forumReply
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ForumReply $forumReply)
    {
        $data = $request->validate([
            'forum_topic_id' =>  ['int','required'],
            'user_id'     =>  ['required'],
            'content'   =>  ['string'],
           ]);

           $forumReply->update($data);
           return response()->json([
            'status'    =>  true,
            'forumReply' => $forumReply,
        ]);
    }
      /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ForumReply  $forumReply
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, ForumReply $forumReply)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);

        $forumReply->update(['status' => $request->boolean('status')]);
        $forumReply->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ForumReply  $forumReply
     * @return \Illuminate\Http\Response
     */
    public function destroy(ForumReply $forumReply)
    {
        $forumReply->delete();
        return response()->json([
            'status'    =>  true,
            "message" => 'Record has been deleted'
        ]);
    }

    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $forumReply = ForumReply::find($id);
            if(Auth::user()->can('delete', $forumReply)) {
                $forumReply->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these front reply ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $forumReply = ForumReply::withTrashed()->find($id);
		if(Auth::user()->can('restore', $forumReply)) {
			$forumReply->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this forum reply ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $forumReply = ForumReply::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $forumReply)) {
			$forumReply->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete this forum reply ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $forumReply = ForumReply::withTrashed()->find($id);
            if(Auth::user()->can('restore', $forumReply)) {
                $forumReply->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these front replies ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $forumReply = ForumReply::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $forumReply)) {
                $forumReply->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these front replies ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
