<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripGroup extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['location_id','trip_id'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
			$query->where(function ($query) use ($search) {
				$query->where('id', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-trip_group", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
                $query->where("creater_id", auth()->user()->id);
        });
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

	public function trips()
	{
		return $this->belongsToMany(Trip::class);
	}
}
