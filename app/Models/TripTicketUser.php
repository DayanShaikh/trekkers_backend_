<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripTicketUser extends Model
{
    use HasFactory, SoftDeletes;

	protected $fillable = ['trip_ticket_id','trip_booking_id','ticket_number','notes', 'status', 'creater_id'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('ticket_number', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-trip_ticket_user", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
        });
    }

	public function tripBooking()
    {
        return $this->belongsTo(TripBooking::class);
    }
}
