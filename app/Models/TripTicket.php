<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripTicket extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['trip_id','airline_id','connecting_flight','type','datum',
    'vluchtnummer','van','naar','vertrek','ankomst','sortorder','status', 'creater_id'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('type', 'like', '%'.$search.'%')
                ->orwhere('datum', 'like', '%'.$search.'%')
                ->orwhere('vluchtnummer', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-trip_ticket", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
        });
    }
    
    public function trip(){
        return $this->belongsTo(Trip::class);
    }

    public function connectingFlight()
    {
        return $this->belongsTo(TripTicket::class, 'connecting_flight');
    }

    public function airline(){
        return $this->belongsTo(Airline::class);
    }
	
	public function tripTicketUsers()
    {
        return $this->hasMany(TripTicketUser::class);
    }
}
