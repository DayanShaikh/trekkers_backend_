<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgeGroup extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['title','sortorder','status','creater_id'];

    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('title', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-age_group", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
                $query->where("creater_id", auth()->user()->id);
        });
    }

    public function trips(){
        return $this->belongsToMany(Trip::class);
    }
}
