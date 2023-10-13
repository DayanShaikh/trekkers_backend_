<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LandingPage extends Model
{
    use HasFactory, SoftDeletes;
	protected $fillable = ['type','month','status','creater_id'];

	public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('id', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-landing_page", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
                $query->where("creater_id", auth()->user()->id);
        });
    }

	public function page()
    {
        return $this->morphOne(Page::class, 'pageable');
    }
}
