<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Location extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['destination_id','title',
    'trip_letter','show_trip_letter','trip_fee','travel_time','upsell_email_title','upsell_email_content','upsell_email_title2',
    'upsell_email_content2','has_flight','icons','require_passport_details','trip_level',
	'included','travel_information','program_details','packing_list','faqs',
	'faqs_new','reviews','review_text','listing_title','listing_text', 'listing_image', 'marketing_text',
	'excursions', 'combination', 'flight', 'meals', 'min_people', 'baggage',
	'sortorder', 'dropbox_link', 'dropbox_link_gids', 'dropbox_folder_name', 'status', 'creater_id'];
    protected $appends = ['listing_image_url', 'min_trip_fee'];
    protected $casts = [
        'icons' => 'array',
    ];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('title', 'ilike', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-location", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
        });
    }
	public function getMinTripFeeAttribute()
	{
        $trips = $this->trips()->select(DB::raw("min(trip_fee) as min"))->where("status", true)->where("start_date", ">=", Carbon::today())->first()->min;
        if(!empty($trips) ){
			return $trips;
		}
		else{
			return $this->trips()->select(DB::raw("min(trip_fee) as min"))->where("status", true)->first()->min;
		}
	}
    public function getListingImageUrlAttribute()
	{
		if(!empty($this->listing_image) ){
			//return url(Storage::url($this->listing_image));
			return config("filesystems.default")=='s3' ? url(Storage::disk("s3")->temporaryUrl($this->listing_image, now()->addMinutes(120))) : url(Storage::url($this->listing_image));
		}
		else{
			return $this->listing_image;
		}
	}

    public function destination(){
        return $this->belongsTo(Destination::class);
    }

    public function attributes(){
        return $this->belongsToMany(Attribute::class);
    }

    public function tripTypes()
    {
        return $this->belongsToMany(TripType::class);
    }

    public function page()
    {
        return $this->morphOne(Page::class, 'pageable');
    }

	public function trips(){
        return $this->hasMany(Trip::class);
    }

	public function locationDays(){
        return $this->hasMany(LocationDay::class);
    }
	
	public function locationPickups(){
        return $this->hasMany(LocationPickup::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
