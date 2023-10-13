<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\EmailTemplateCondition;
use Illuminate\Http\Request;
use Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class EmailTemplateConditionController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(EmailTemplateCondition::class, 'email_template_condition');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return EmailTemplateCondition::query()
		->with('emailTemplate')
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
			'booking_days_before_start_date' 	=> ['string','required'],
            'days_after_booking'   				=>  [''],
			'days_before_departure'   			=>  [''],
			'type'   							=>  [''],
			'email_template_id'   				=>  [''],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $emailTemplateCondition = EmailTemplateCondition::create($data);
        $emailTemplateCondition->save();
        return response()->json([
            'status'    =>  true,
            'emailTemplateCondition' => $emailTemplateCondition,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EmailTemplateCondition  $emailTemplateCondition
     * @return \Illuminate\Http\Response
     */
    public function show(EmailTemplateCondition $emailTemplateCondition)
    {
		$emailTemplateCondition = $emailTemplateCondition->with('emailTemplate')->where('id', $emailTemplateCondition->id)->first();
        return response()->json(['status' => true, 'emailTemplateCondition' => $emailTemplateCondition]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EmailTemplateCondition  $emailTemplateCondition
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmailTemplateCondition $emailTemplateCondition)
    {
        $data = $request->validate([
            'booking_days_before_start_date' 	=> ['string','required'],
            'days_after_booking'   				=>  [''],
			'days_before_departure'   			=>  [''],
			'type'   							=>  [''],
			'email_template_id'   				=>  [''],
        ]);
        $emailTemplateCondition->update($data);
        $emailTemplateCondition->save();
        return response()->json([
            'status'    =>  true,
            'emailTemplateCondition' => $emailTemplateCondition,
        ]);
    }

     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EmailTemplateCondition  $emailTemplateCondition
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, EmailTemplateCondition $emailTemplateCondition)
	{
		$validated = $request->validate([
			'status' => ['required'],
		]);

		$emailTemplateCondition->update(['status' => $request->boolean('status')]);
		$emailTemplateCondition->save();

		return response()->json(['status' => true, 'message' => "Status has been updated"]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmailTemplateCondition  $emailTemplateCondition
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmailTemplateCondition $emailTemplateCondition)
    {
        $emailTemplateCondition->delete();
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
            $emailTemplateCondition = EmailTemplateCondition::find($id);
            if(Auth::user()->can('delete', $emailTemplateCondition)) {
                $emailTemplateCondition->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these email template condition ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
	}

    public function restore($id)
    {
        $emailTemplateCondition = EmailTemplateCondition::withTrashed()->find($id);
		if(Auth::user()->can('restore', $emailTemplateCondition)) {
			$emailTemplateCondition->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore this email template condition ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $emailTemplateCondition = EmailTemplateCondition::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $emailTemplateCondition)) {
			$emailTemplateCondition->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete this email template condition ID: '.$id], 403);
		}
    }
    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $emailTemplateCondition = EmailTemplateCondition::withTrashed()->find($id);
            if(Auth::user()->can('restore', $emailTemplateCondition)) {
                $emailTemplateCondition->restore();
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
            $emailTemplateCondition = EmailTemplateCondition::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $emailTemplateCondition)) {
                $emailTemplateCondition->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanant delete these email template conditions ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
