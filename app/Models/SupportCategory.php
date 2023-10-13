<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class SupportCategory extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['title','icon','sortorder','status','creater_id'];

	protected $appends = ['icon_url'];

	public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('title', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-support_category", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
			$query->where("creater_id", auth()->user()->id);
		});
    }

	public function getIconUrlAttribute()
	{
		if(!empty($this->icon) ){
			return url(Storage::url($this->icon));
		}
		else{
			return $this->icon;
		}
	}

	public function page()
    {
        return $this->morphOne(Page::class, 'pageable');
    }

	public function supportArticles()
    {
        return $this->hasMany(SupportArticle::class)->where('status', true);
    }
}
