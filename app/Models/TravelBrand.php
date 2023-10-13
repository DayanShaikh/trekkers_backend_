<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelBrand extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['travel_admin_id','brand_name','email','password','commission_type','status','creater_id'];

    public function scopeFilter($query,array $filters)
    {
    $query->when($filters['search']?? null,function($query,$search){
        $query->where(function ($query) use ($search) {
            $query->where('brand_name', 'like', '%'.$search.'%');
        });
    })->when(!in_array("viewAny-travel_brand", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
        $query->where("creater_id", auth()->user()->id);
    });
    }

    public function travelAdmin()
    {        
        return $this->belongsTo(TravelAdmin::class);
    }
}
