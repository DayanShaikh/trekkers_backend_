<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripBookingAddon extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['trip_booking_id','location_addon_id','booking_date','amount','amount_paid',
    'payment_date','processed','notes','extra_field_1','extra_field_2','extra_field_3','status', 'creater_id'];

    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('processed', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-trip_booking_addon", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
    });
    }
    public function tripBooking()
    {
        return $this->belongsTo(TripBooking::class);
    }
    public function locationAddon()
    {
        return $this->belongsTo(LocationAddon::class);
    }
    public function orders(){
        return $this->morphMany(RabobankOrder::class, "orderable");
    }
}
