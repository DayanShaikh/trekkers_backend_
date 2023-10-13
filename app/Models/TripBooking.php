<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripBooking extends Model
{
	use HasFactory, SoftDeletes;
            protected $fillable = ['user_id','trip_id','child_firstname',
            'child_lastname','gender','child_dob','parent_email','email','address','house_number','city',
            'postcode','telephone','cellphone','date_added','whatsapp_number','parent_name','location_pickup_id','child_diet',
            'child_medication','about_child','can_drive','have_driving_license','have_creditcard',
            'trip_fee','insurance','cancellation_insurance','travel_insurance','cancellation_policy_number',
            'travel_policy_number','survival_adventure_insurance','insurance_admin_charges',
            'nature_disaster_insurance','sgr_contribution','insurnace_question_1','insurnace_question_2',
            'total_amount','paid_amount','deleted','payment_reminder_email_sent','total_reminder_sent',
            'email_sent','login_reminder_email_sent','upsell_email_sent','deposit_reminder_email_sent',
            'passport_reminder_email_sent','display_name','additional_address','contact_person_name',
            'contact_person_extra_name','contact_person_extra_cellphone','travel_agent_email',
            'commission','covid_option','account_name','account_number','phone_reminder_email_sent','country','invoice_number','status','creater_id'];

	public function scopeFilter($query,array $filters)
	{
		$query->when($filters['search']?? null,function($query, $search){
			$query->where(function ($query) use ($search) {
				$query->where('child_firstname', 'like', '%'.$search.'%')
				->orWhere('child_lastname', 'like', '%'.$search.'%')
				->orWhere('email', 'like', '%'.$search.'%')
				->orWhere('id', 'like', '%'.$search.'%')
				;
			});
		})->when(!in_array("viewAny-trip_booking", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
			$query->where("creater_id", auth()->user()->id);
		});
	}
	
	protected $casts = [
        'insurance' => 'json',
		'date_added'  => 'date:Y-m-d',
		'child_dob'  => 'date:Y-m-d',
    ];

    protected $appends = ['partial_payment_amount'];

    public function getPartialPaymentAmountAttribute(){
        $depositPercent = ConfigVariable::where("config_key", "deposit_percent")->first();
        return round( ($this->total_amount * $depositPercent->value) / 100 , 2);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
    public function travelAgent()
    {
        return $this->belongsTo(TravelAgent::class);
    }
    public function travelBrand()
    {
        return $this->belongsTo(TravelBrand::class);
    }
	public function passportDetails(){
        return $this->hasMany(PassportDetail::class);
    }
	public function tripTicketUsers(){
        return $this->hasMany(TripTicketUser::class);
    }
    public function orders(){
        return $this->morphMany(RabobankOrder::class, "orderable");
    }
	public function locationAddon()
    {
        return $this->belongsTo(LocationAddon::class);
    }
	public function locationPickup()
    {
        return $this->belongsTo(LocationPickup::class);
    }
	public function notes(){
        return $this->hasMany(TripBookingNote::class);
    }
}