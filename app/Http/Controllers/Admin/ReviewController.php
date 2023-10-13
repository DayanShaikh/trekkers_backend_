<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
	 * Create the controller instance.
	 *
	 * @return void
	 */
	 public function __construct()
	 {
	 	$this->authorizeResource(Review::class, 'review');
	 }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Review::with('tripBooking', 'tripBooking.trip', 'tripBooking.trip.location')
        ->when('tripBooking', function ($q) use($request){
            if($request->trip_booking_id) {
                $q->where('trip_booking_id', $request->trip_booking_id );
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
        ->orderBy($request->order_by ?? 'created_at', $request->order ?? 'desc')
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
            'trip_booking_id' => ['int'],
            'fake_trip_booking_id' => [''],
            'review_date'   =>  [''],
            'tour_guide_points'  =>  ['required','int'],
            'quality_price_points'  =>  ['required','int'],
            'activities_points'  =>  ['required','int'],
            'review_text'  =>  [''],
            'feedback_text'  =>  [''],
            'show_client_details'  =>  [''],
        ]);
        $data['creater_id'] =   auth()->user()->id;
        $review = Review::create($data);
        if ($request->hasFile('review_picture')){
			if(!empty($review->review_picture)){
				Storage::delete($review->review_picture);
			}
			$review->review_picture = Storage::putFile('public/reviews/', $request->file('review_picture'));
            $review->save();
		}
        return response()->json([
            'status'    =>  true,
            'review' => $review,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function show(Review $review)
    {
		$review = $review->with('tripBooking')->where('id', $review->id)->first();
        return response()->json(['status' => true, 'review' => $review]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Review $review)
    {
        $data = $request->validate([
            'trip_booking_id' => ['int'],
            'fake_trip_booking_id' => [''],
            'review_date'   =>  [''],
            'tour_guide_points'  =>  [''],
            'quality_price_points'  =>  [''],
            'activities_points'  =>  [''],
            'review_text'  =>  [''],
            'feedback_text'  =>  [''],
            'show_client_details'  =>  [''],
        ]);
        $review->update($data);
        if ($request->hasFile('review_picture')){
			if(!empty($review->review_picture)){
				Storage::delete($review->review_picture);
			}
			$review->review_picture = Storage::putFile('public/reviews/', $request->file('review_picture'));
            $review->save();
		}
        return response()->json([
            'status'    =>  true,
            'review' => $review,
        ]);
    }

     /**
     * Update the Lock Status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function updateActiveStatus(Request $request, Review $review)
	{
        $validated = $request->validate([
			'status' => ['required'],
		]);

        $review->update(['status' => $request->boolean('status')]);
        $review->save();
        return response()->json([
            'status' => true,
            'message'   =>  'Status has been updated'
        ]);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function destroy(Review $review)
    {
        $review->delete();
        return response()->json([
			'status' => true,
            'message'   =>  'Record has been deleted',
		]);
    }

    public function massDestroy(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $review = Review::find($id);
            if(Auth::user()->can('delete', $review)) {
                $review->delete();
                $count++;
            }
            else{
                $errors[] = ["You do not have permission to delete Review ID: ".$id.""];
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to delete these reviews ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been deleted'
        ]);
    }

    public function restore($id)
    {
        $review = Review::withTrashed()->find($id);
		if(Auth::user()->can('restore', $review)) {
			$review->restore();
			return response()->json([
                'status' => true,
                'message'   =>  'Record has been restored'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' => 'You do not have permission to restore review ID: '.$id,
            ], 403);
		}
    }

    public function forceDelete($id)
    {
        $review = Review::withTrashed()->find($id);
		if(Auth::user()->can('forceDelete', $review)) {
			if(!empty($review->review_picture)){
                Storage::delete($review->review_picture);
            }
			$review->forceDelete();
			return response()->json([
                'status' => true,
                'message'   =>  'Record has been permanent deleted'
            ]);
		}
		else {
			return response([
                'status' => false,
                'error' =>  'You do not have permission to permanent delete review ID: '.$id], 403);
		}
    }

    public function massRestore(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $review = Review::withTrashed()->find($id);
            if(Auth::user()->can('restore', $review)) {
                $review->restore();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to restore these page reviews ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been restored'
        ]);
	}

    public function massForceDelete(Request $request)
	{
        $count = 0;
        $errors = [];
        foreach($request->get('ids') as $id) {
            $review = Review::withTrashed()->find($id);
            if(Auth::user()->can('forceDelete', $review)) {
				if(!empty($review->review_picture)){
					Storage::delete($review->review_picture);
				}
                $review->forceDelete();
                $count++;
            }
            else{
                $errors[] = $id;
            }
        }
        return response()->json([
            "status" => true,
            "count" => $count,
            "errors" => $errors ? "You do not have permission to permanent delete these reviews ID: [ ". implode(",",$errors)." ]": "",
            "message" => 'Selected record has been permanent deleted'
        ]);
	}
}
