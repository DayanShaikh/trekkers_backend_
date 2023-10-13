<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Review extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['trip_booking_id','fake_trip_booking_id','review_date','tour_guide_points',
    'quality_price_points','activities_points','review_text','feedback_text','review_picture',
    'show_client_details','status','creater_id'];
	protected $appends = ['review_picture_url'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('review_text', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-review", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
			$query->where("creater_id", auth()->user()->id);
		});
    }
	public function getReviewPictureUrlAttribute()
	{
		if(!empty($this->review_picture) ){
			return url(Storage::url($this->review_picture));
		}
		else{
			return $this->review_picture;
		}
	}
    public function tripBooking()
    {
        return $this->belongsTo(TripBooking::class);
    }
    
}
