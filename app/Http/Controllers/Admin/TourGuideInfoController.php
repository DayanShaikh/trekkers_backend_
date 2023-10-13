<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\TourGuideInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TourGuideInfoController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(TourGuideInfo::class, 'tour_guide_info');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return TourGuideInfo::query()
        ->when($request->get("trash"), function($query) use ($request){
            if($request->get('trash')==1){
                $query->onlyTrashed();
            }
        })
        ->when($request->get("status") != null, function($query) use ($request){
            $query->where('status', $request->status );    
        })
        ->filter($request->only('search'))
        ->orderBy($request->order_by ?? 'house_number', $request->order ?? 'desc')
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
            'datetime_added' =>  [''],
            'dob'   =>  [''],
            'street_name'  =>  ['required'],
            'house_number'  =>  ['required'],
            'residence'  =>  [''],
            'telephone'  =>  [''],
            'emergency_contact_name'  =>  [''],
            'emergency_contact_number'  =>  [''],
            'first_name'  =>  [''],
            'last_name'  =>  [''],
            'id_card_number'  =>  [''],
            'expiry_date'  =>  [''],
            'availability'  =>  [''],
            'bank_account_number'  =>  [''],
            'email'  =>  [''],
            'expiry_date_passport'  =>  [''],

        ]);
        $data['created_id'] =   auth()->user()->id;
        $tourGuideInfo = TourGuideInfo::create($data);
        if ($request->hasFile('passport_image')){
			if(!empty($tourGuideInfo->passport_image)){
				Storage::delete($tourGuideInfo->passport_image);
			}
			$tourGuideInfo->passport_image = Storage::putFile('public/tour_guide_infos/', $request->file('passport_image'));
            $tourGuideInfo->save();
		}
        return response()->json([
            'status'    =>  true,
            'tourGuideInfo' => $tourGuideInfo,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TourGuideInfo  $tourGuideInfo
     * @return \Illuminate\Http\Response
     */
    public function show(TourGuideInfo $tourGuideInfo)
    {
        return response()->json(['status' => true, 'tourGuideInfo' => $tourGuideInfo]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TourGuideInfo  $tourGuideInfo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TourGuideInfo $tourGuideInfo)
    {
        $data = $request->validate([
            'datetime_added' =>  [''],
            'dob'   =>  [''],
            'street_name'  =>  ['required'],
            'house_number'  =>  ['required'],
            'residence'  =>  [''],
            'telephone'  =>  [''],
            'emergency_contact_name'  =>  [''],
            'emergency_contact_number'  =>  [''],
            'first_name'  =>  [''],
            'last_name'  =>  [''],
            'id_card_number'  =>  [''],
            'expiry_date'  =>  [''],
            'availability'  =>  [''],
            'bank_account_number'  =>  [''],
            'email'  =>  [''],
            'expiry_date_passport'  =>  [''],

        ]);
        $tourGuideInfo->update($data);
        if ($request->hasFile('passport_image')){
			if(!empty($tourGuideInfo->passport_image)){
				Storage::delete($tourGuideInfo->passport_image);
			}
			$tourGuideInfo->passport_image = Storage::putFile('public/tour_guide_infos/', $request->file('passport_image'));
            $tourGuideInfo->save();
		}
        return response()->json([
            'status'    =>  true,
            'tourGuideInfo' => $tourGuideInfo,
        ]);
    }

     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TourGuideInfo  $tourGuideInfo
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, TourGuideInfo $tourGuideInfo)
	{
		$validated = $request->validate([
			'status' => ['required'],
		]);

		$tourGuideInfo->update(['status' => $request->boolean('status')]);
		$tourGuideInfo->save();

		return response()->json(['status' => true, 'message' => "Status has been updated"]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TourGuideInfo  $tourGuideInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(TourGuideInfo $tourGuideInfo)
    {
        $tourGuideInfo->delete();
        return response()->json([
			'status' => true,
		]);
    }

    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tourGuideInfo = TourGuideInfo::find($id);
            if(Auth::user()->can('delete', $tourGuideInfo)) {
                $tourGuideInfo->delete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to delete Tour Guide Info ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors
        ]);
    }

    public function restore($id)
    {
        $tourGuideInfo = TourGuideInfo::withTrashed()->find($id);
		if(Auth::user()->can('restore', $tourGuideInfo)) {
			$tourGuideInfo->restore();
			return response()->json(['status' => true]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore Support Category ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete(Request $request)
    {
        $tourGuideInfo = SupportCategory::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $tourGuideInfo)) {
			if(!empty($tourGuideInfo->passport_image)){
                Storage::delete($tourGuideInfo->passport_image);
            }
			$tourGuideInfo->forceDelete();
			return response()->json(['status' => true]);
		}
		else {
			return response(['status' => 'You do not have permission to Permanent Delete Tour Guide Info ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tourGuideInfo = TourGuideInfo::withTrashed()->find($id);
            if(Auth::user()->can('restore', $tourGuideInfo)) {
                $tourGuideInfo->restore();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Restore Tour Guide Info ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors
        ]);
	}
    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $tourGuideInfo = TourGuideInfo::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $tourGuideInfo)) {
                if(!empty($tourGuideInfo->passport_image)){
                    Storage::delete($tourGuideInfo->passport_image);
                }
				$tourGuideInfo->forceDelete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to Permanent Delete Tour Guide Info ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors,
        ]);
	}
}
