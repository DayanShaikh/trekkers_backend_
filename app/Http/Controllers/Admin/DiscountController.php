<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class DiscountController extends Controller
{
     /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(Discount::class, 'discount');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Attribute::query()
        ->with('location')
        ->when('location', function ($q) use($request){
            if($request->location_id) {
                $q->where('location_id', $request->location_id );
            }
        })
        ->when($request->get('validity_date'), function ($query) use($request){
			$query->where("validity_date", $request->get('validity_date'));
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
        ->orderBy($request->order_by ?? 'discount_code', $request->order ?? 'desc')
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
            'location_id' => ['int', 'required'],
            'discount_code' => ['string','required'],
            'discount_amount' =>  ['int','required'],
            'validity_date'   =>  ['required'],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $discount = Discount::create($data); 
        return response()->json([
            'status'    =>  true,
            'discount' => $discount,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function show(Discount $discount)
    {
        return response()->json(['status' => true, 'discount' => $discount]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Discount $discount)
    {
        $data = $request->validate([
            'location_id' => ['int'],
            'discount_code' => ['string','required'],
            'discount_amount' =>  ['int','required'],
            'validity_date'   =>  ['required'],
        ]);
        $data['creater_id'] = auth()->user()->id;
        $discount = Discount::create($data); 
        return response()->json([
            'status'    =>  true,
            'discount' => $discount,
        ]);
    }

    
     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, Discount $discount)
	{
		$validated = $request->validate([
			'status' => ['required'],
		]);

		$discount->update(['status' => $request->boolean('status')]);
		$discount->save();

		return response()->json(['status' => true, 'message' => "Status has been updated"]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function destroy(Discount $discount)
    {
        $discount->delete();
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
            $discount = Discount::find($id);
            if(Auth::user()->can('delete', $discount)) {
                $discount->delete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these discounts ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
	}

    public function restore($id)
    {
        $discount = Discount::withTrashed()->find($id);
		if(Auth::user()->can('restore', $discount)) {
			$discount->restore();
			return response()->json([
                'status' => true,
                "message" => 'Record has been restored'
            ]);
		}
		else {
			return response(['status' => false], 403);
		}
    }

    public function forceDelete($id)
    {
        $discount = Discount::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $discount)) {
			$discount->forceDelete();
			return response()->json([
                'status' => true,
                "message" => 'Record has been permanent deleted'
            ]);
		}
		else {
			return response(['status' => false], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $discount = Discount::withTrashed()->find($id);
            if(Discount::user()->can('restore', $discount)) {
                $discount->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these discounts ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}
    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $discount = Discount::withTrashed()->find($id);
            if(Discount::user()->can('forceDelete', $discount)) {
                $discount->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these discounts ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
