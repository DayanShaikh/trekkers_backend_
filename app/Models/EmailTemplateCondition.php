<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplateCondition extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable =['booking_days_before_start_date', 'days_after_booking', 'days_before_departure', 'type', 'email_template_id', 'status', 'creater_id'];

    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('booking_days_before_start_date', 'like', '%'.$search.'%')
				->orwhere('days_after_booking', 'like', '%'.$search. '%')
				->orwhere('days_before_departure', 'like', '%'.$search. '%');
            });
        })->when(!in_array("viewAny-email_template_condition", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
                $query->where("creater_id", auth()->user()->id);
        });
    }

	public function emailTemplate(){
        return $this->belongsTo(EmailTemplate::class);
    }
}
