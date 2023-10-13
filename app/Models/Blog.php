<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kingmaker\Illuminate\Eloquent\Relations\HasBelongsToManySelfRelation;

class Blog extends Model
{

    use HasFactory, SoftDeletes, HasBelongsToManySelfRelation;
    protected $fillable = ['title','seo_url','excerpt','content','image','date','meta_title',
    'meta_description','published_at','meta_keywords','related_post','status','creater_id'];

    protected $appends = ['image_url'];

    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('title', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-blog", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
    });
    }

    public function getImageUrlAttribute()
	{
		if(!empty($this->image) ){
			return url(Storage::url($this->image));
		}
		else{
			return $this->image;
		}
	}

    public function blogCategories()
    {
        return $this->belongsToMany(BlogCategory::class);
    }

    public function relatedBlogs()
    {
        return $this->belongsToManySelf('related_blogs', 'blog1', 'blog2');
    }
}
