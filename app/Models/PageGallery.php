<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PageGallery extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable=['page_id','title','image', 'dropbox_image', 'sortorder','status', 'creater_id'];
    protected $appends = ['image_url'];
    
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('title', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-page_gallery", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
        });
    }

    public function getImageUrlAttribute()
	{
		if(!empty($this->image) ){
			return config("filesystems.default")=='s3' ? url(Storage::disk("s3")->temporaryUrl($this->image, now()->addMinutes(120))) : url(Storage::url($this->image));
		}
		
	}

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
