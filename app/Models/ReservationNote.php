<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationNote extends Model
{
	use HasFactory, SoftDeletes;
    protected $fillable = ['reservation_id','notes','status','creater_id'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
                $query->where('notes', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-reservation_note", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
    });
    }
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
