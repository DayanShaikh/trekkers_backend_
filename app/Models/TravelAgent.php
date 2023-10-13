<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelAgent extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['firstname','lastname','email','username','password','travelclub_number','status','creater_id'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('firstname', 'like', '%'.$search.'%')
                ->orwhere('lastname', 'like', '%'.$search.'%')
                ->orwhere('username', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-travel_agent", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
        });
    }
}
