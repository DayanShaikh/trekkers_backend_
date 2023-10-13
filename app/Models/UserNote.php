<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserNote extends Model
{
	use HasFactory, SoftDeletes;
    protected $fillable = ['user_id','trip_id', 'status', 'notes'];

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('notes','like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-user_note", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
			$query->where("creater_id", auth()->user()->id);
		});
    }

	public function trip(){
        return $this->belongsTo(Trip::class);
    }
}
