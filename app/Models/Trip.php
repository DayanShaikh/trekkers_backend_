<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Trip extends Model
{
    use HasFactory, SoftDeletes;
	
    protected $fillable = ['location_id','total_space','male_female_important',
    'show_client_detail','start_date','duration','trip_fee', 'trip_letter','custom_trip_letter',
    'trip_seats_status','is_not_bookable','archive','is_full','status','creater_id'];
    
    protected $appends = ['space'];
	protected $casts = [
		'start_date'  => 'date:Y-m-d',
    ];

    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
			$query->where(function ($query) use ($search) {
				$query->where('id', 'like', '%'.$search.'%')
                ->orwhere('start_date', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-trip", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
        });
    }

    public function scopeActive($query)
    {
        $query->where("status", true)->where("start_date", ">=", Carbon::today());
    }

    public function getSpaceAttribute(){
        $maleBookings = $this->bookings()->where("gender", false)->where("status", true)->whereNull('deleted_at')->count();
        $femaleBookings = $this->bookings()->where("gender", true)->where("status", true)->whereNull('deleted_at')->count();
        $maleReservations = $this->reservations()->active()->where("status", true)->where("gender", false)->count();
        $femaleReservations = $this->reservations()->active()->where("status", true)->where("gender", true)->count();
        $space_used = [
            "remaining" => $this->total_space - $maleBookings - $femaleBookings - $maleReservations - $femaleReservations,
            "male" => $maleBookings + $maleReservations,
            "female" => $femaleBookings + $femaleReservations,
        ];
        if($space_used["remaining"]<0){
			$space_used["remaining"] = 0;
			$space_used["male_remaining"] = 0;
			$space_used["female_remaining"] = 0;
		}
		else{
			$space_used["male_remaining"] = $space_used["male"]%2;
			$space_used["female_remaining"] = $space_used["female"]%2;
		}
		$remaining2 = $space_used["remaining"] - $space_used["male_remaining"] - $space_used["female_remaining"];
		if($remaining2<0){
			$remaining2 = 0;
		}
        $space_used["male_remaining"] += $remaining2/2;
        $space_used["female_remaining"] += $remaining2/2;
        return (object)$space_used;
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function ageGroups()
    {
        return $this->belongsToMany(AgeGroup::class);
    }
	
	public function tripTickets(){
        return $this->hasMany(TripTicket::class);
    }

    public function bookings(){
        return $this->hasMany(TripBooking::class);
    }

    public function reservations(){
        return $this->hasMany(Reservation::class);
    }
}
