<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class EmailTemplateController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->authorizeResource(EmailTemplate::class, 'email_template');
	}

   /**
	* Display a listing of the resource.
	*
	* @return \Illuminate\Http\Response
	*/
   public function index(Request $request)
   {
	   return EmailTemplate::query()
	   	->when($request->get("trash"), function($query) use ($request){
			if($request->get('trash')==1){
				$query->onlyTrashed();
			}
		})
        ->when($request->get("status") != null, function($query) use ($request){
            $query->where('status', $request->status );    
        })
		->filter($request->only('search'))
	   ->orderBy($request->order_by ?? 'created_at', $request->order ?? 'desc')
	   ->paginate($request->per_page ?? 10)
	   ;
   }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'subject'   =>  [''],
			'body'   =>  [''],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $emailTemplate = EmailTemplate::create($data);
        $emailTemplate->save();
        return response()->json([
            'status'    =>  true,
            'emailTemplate' => $emailTemplate,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(EmailTemplate $emailTemplate)
    {
        return response()->json(['status' => true, 'emailTemplate' => $emailTemplate]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $data = $request->validate([
            'title' => ['string','required'],
            'subject'   =>  [''],
			'body'   =>  [''],
        ]);
        $emailTemplate->update($data);
        $emailTemplate->save();
        return response()->json([
            'status'    =>  true,
            'emailTemplate' => $emailTemplate,
        ]);
    }

/**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, EmailTemplate $emailTemplate)
	{
		$validated = $request->validate([
			'status' => ['required'],
		]);

		$emailTemplate->update(['status' => $request->boolean('status')]);
		$emailTemplate->save();

		return response()->json(['status' => true, 'message' => "Status has been updated"]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();
		return response()->json([
			'status' => true,
            "message" => 'Record has been deleted'
		]);
    }

    public function massDestroy (Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $emailTemplate = EmailTemplate::find($id);
            if(Auth::user()->can('delete', $emailTemplate)) {
                $emailTemplate->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these email template ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
	}

	public function restore($id)
    {
        $emailTemplate = EmailTemplate::withTrashed()->find($id);
		if(Auth::user()->can('restore', $emailTemplate)) {
			$emailTemplate->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this email template ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $emailTemplate = EmailTemplate::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $emailTemplate)) {
			$emailTemplate->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete this email template ID: '.$id], 403);
		}
    }
    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $emailTemplate = EmailTemplate::withTrashed()->find($id);
            if(Auth::user()->can('restore', $emailTemplate)) {
                $emailTemplate->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these sttributes ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $emailTemplate = EmailTemplate::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $emailTemplate)) {
                $emailTemplate->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanant delete these email templates ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
