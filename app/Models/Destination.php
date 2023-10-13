<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Destination extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['title','iso_code','travel_insurance_fees','is_survival_adventure_insurance_active',
	'intro_title','intro_text','intro_video','video_text','header_video_id','trip_title','other_trip_title','trip_toggle','thumb_image',
	'sortorder','status','creater_id', 'color_code', 'unicode'];

	protected $appends = ['thumb_image_url'];

    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('title', 'ilike', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-destination", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
			$query->where("creater_id", auth()->user()->id);
		});
    }

	public function getThumbImageUrlAttribute()
	{
		if(!empty($this->thumb_image) ){
			return config("filesystems.default")=='s3' ? url(Storage::disk("s3")->temporaryUrl($this->thumb_image, now()->addMinutes(120))) : url(Storage::url($this->thumb_image));
		}
		else{
			return $this->thumb_image;
		}
	}

    public function page()
    {
        return $this->morphOne(Page::class, 'pageable');
    }

	public function locations(){
        return $this->hasMany(Location::class);
    }

	public function trips(){
        return $this->belongsToMany(Location::class)->where('type', 0);
    }

	public function otherTrips(){
        return $this->belongsToMany(Location::class)->where('type', 1);
    }

	public function headerVideo(){
        return $this->belongsTo(HeaderVideo::class);
    }

}
