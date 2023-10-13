<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['title','seo_url_path','travel_insurance_fees','is_survival_adventure_insurance_active','sortorder','status','creater_id'];

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('title', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-attribute", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
                $query->where("creater_id", auth()->user()->id);
        });
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }
}
