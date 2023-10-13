<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PageDag extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable=['page_id','title','image','description','sortorder','status', 'creater_id'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('title', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-page_dag", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
        });
    }
    public function getImage()
	{
		if(!empty($this->image) ){
			return url(Storage::url($this->image));
		}
		else{
			return $this->image;
		}
	}
    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
