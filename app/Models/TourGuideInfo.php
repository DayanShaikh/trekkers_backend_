<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class TourGuideInfo extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['datetime_added','dob','street_name','house_number',
    'residence','telephone','emergency_contact_name','emergency_contact_number',
    'first_name','last_name','id_card_number','expiry_date','availability','licence_image',
    'bank_account_number','email','passport_image','expiry_date_passport','status','creater_id'];

    public function scopeFilter($query,array $filters)
    {
    $query->when($filters['search']?? null,function($query,$search){
        $query->where(function ($query) use ($search) {
            $query->where('house_number', 'like', '%'.$search.'%');
        });
    })->when(!in_array("viewAny-tour_guide_info", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
        $query->where("creater_id", auth()->user()->id);
    });
    }
}
