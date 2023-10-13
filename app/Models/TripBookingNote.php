<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripBookingNote extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['trip_booking_id','notes', 'is_log', 'is_publish', 'status','creater_id'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
                $query->where('notes', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-trip_booking_note", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
    });
    }
    public function tripBooking()
    {
        return $this->belongsTo(TripBooking::class);
    }
}
