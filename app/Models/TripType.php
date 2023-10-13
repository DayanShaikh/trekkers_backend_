<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes; 

class TripType extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['title','image','show_on_homepage','sortorder','description',
   'status','creater_id'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
	{
        if(!empty($this->image) ){
			return config("filesystems.default")=='s3' ? url(Storage::disk("s3")->temporaryUrl($this->image, now()->addMinutes(120))) : url(Storage::url($this->image));
		}
		else{
			return $this->image;
		}
	}

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('title','ilike', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-trip_type", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
			$query->where("creater_id", auth()->user()->id);
		});
    }

    public function page()
    {
        return $this->morphOne(Page::class, 'pageable');
    }
}
