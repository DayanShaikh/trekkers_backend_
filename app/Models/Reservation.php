<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['trip_id','trip_booking_id','user_id','child_firstname','child_lastname',
'gender','child_dob','parent_name','parent_email','email','address','house_number','city',
'postcode','telephone','cellphone','whatsapp_number','location_pickup_id','child_diet',
'child_medication','about_child','date_added','can_drive','have_driving_license','have_creditcard',
'trip_fee','total_amount','paid_amount','deleted','payment_reminder_email_sent',
'email_sent','login_reminder_email_sent','upsell_email_sent','deposit_reminder_email_sent',
'display_name','additional_address','contact_person_name','contact_person_extra_name',
'contact_person_extra_cellphone', 'country', 'reservation_fees','reservation_fees_paid_at','reservation_fees_payment_type',
'expiry_date','status','creater_id'];
	protected $casts = [
		'date_added'  => 'date:Y-m-d',
		'child_dob'  => 'date:Y-m-d',
		'expiry_date'  => 'date:Y-m-d',
    ];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('child_firstname', 'like', '%'.$search.'%')
                ->orwhere('child_lastname', 'like', '%'.$search. '%');
            });
        })->when(!in_array("viewAny-reservation", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
			$query->where("creater_id", auth()->user()->id);
		});
    }

    public function scopeActive($query){
        return $query;
    }

    public function tripBooking()
    {
        return $this->belongsTo(TripBooking::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
    public function orders(){
        return $this->morphMany(RabobankOrder::class, "orderable");
    }
}
