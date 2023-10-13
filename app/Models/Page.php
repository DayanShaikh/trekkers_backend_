<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
use Storage;

class Page extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['pageable_type', 'pageable_id', 'page_name','title','content','highlights',
                            'image','meta_title','meta_description','meta_keywords',
                            'sitemap_title','sitemap_details',
                            'header_details','show_schema_markup',
                            'schema_title', 'show_search_box', 'status','creater_id'];

// protected $hidden = ['pageable_type', 'pageable_id'];

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
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('title', 'like', '%'.$search.'%')
                ->orwhere('page_name', 'like', '%'.$search. '%');
            });
        })->when(!in_array("viewAny-page", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
                $query->where("creater_id", auth()->user()->id);
        });
    }
    
    public function pageGalleries()
    {
        return $this->hasMany(PageGallery::class);
    }

    public function pageCountry()
    {
        return $this->hasOne(PageCountry::class);
    }

    public function pageable()
	{
		return $this->morphTo();
	}
}
