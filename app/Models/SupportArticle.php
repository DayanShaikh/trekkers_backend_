<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportArticle extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable =['support_category_id','title','seo_url','excerpt','date','time', 'sortorder','status','creater_id'];
    protected $appends = ['category_url'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('title', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-support_article", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
			$query->where("creater_id", auth()->user()->id);
		});
    }
    public function getCategoryUrlAttribute()
	{
        $trips = $this->supportCategory()->with(['page' => function($q){
            $q->select('id', 'pageable_type', 'pageable_id', 'page_name');
        }])->first();
        if(!empty($trips) ){
			return $trips;
		}
		else{
			return '--';
		}
	}
    public function supportCategory()
    {
        return $this->belongsTo(SupportCategory::class);
    }

	public function page()
    {
        return $this->morphOne(Page::class, 'pageable');
    }
}
