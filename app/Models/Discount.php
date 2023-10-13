<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable =['location_id', 'discount_code', 'discount_amount', 'validity_date', 'status', 'creater_id'];

    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('discount_code', 'like', '%'.$search.'%');
            });     
        })->when(!in_array("viewAny-discount", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
                $query->where("creater_id", auth()->user()->id);
        });
    } 

    public function location(){
        return $this->belongsTo(Location::class);
    }
}
