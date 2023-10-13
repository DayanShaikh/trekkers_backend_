<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripBookingExtraInsurance extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['trip_booking_id','date','insurance','survival_adventure_insurance',
    'travel_insurance','insurance_admin_charges','is_completed','payment_date','status', 'creater_id'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('insurance', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-trip_booking_extra_insurance", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
    });
    }

    public function tripBooking()
    {
        return $this->belongsTo(TripBooking::class);
    }
}
