<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PageRedirect extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['page_url', 'redirect_url', 'status','creater_id'];

    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('page_url', 'like', '%'.$search.'%')
                ->orwhere('redirect_url', 'like', '%'.$search. '%');
            });
        })->when(!in_array("viewAny-page_redirect", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
                $query->where("creater_id", auth()->user()->id);
        });
    }
}
