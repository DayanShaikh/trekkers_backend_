<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class LocationAddon extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['location_id','title','image','mobile_image','description','price','is_public',
    'hide_payment','sortorder','extra_field_1','extra_field_2','extra_field_3','status','creater_id'];

    protected $appends = ['image_url', 'mobile_image_url'];

    public function getImageUrlAttribute(){
        if(!empty($this->image) ){
            return url(Storage::url($this->image));
        }
        else{
            return $this->image;
        }
    }
    
    public function getMobileImageUrlAttribute(){
        if(!empty($this->mobile_image) ){
            return url(Storage::url($this->mobile_image));
        }
        else{
            return $this->mobile_image;
        }
    }

    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-location_addon", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
                $query->where("creater_id", auth()->user()->id);
        });
    }
    public function location()
    {
        return $this->belongsTo(Location::class);
    }    
}
