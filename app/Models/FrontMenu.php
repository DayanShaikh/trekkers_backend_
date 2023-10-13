<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FrontMenu extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['position','title','url','parent_id','sortorder','is_default','status', 'creater_id'];
    protected $appends = ['image_path'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('title', 'ilike', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-front_menu", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
        });
    }
    public function getImagePathAttribute(){
        if($this->image){
            return $this->image;
        }
        else if($this->url){
            $page = Page::where("page_name", str_replace(".html", "", $this->url))->first();
            if($page){
                if($page->pageable_type ===  'App\\Models\\Destination'){
                    return $page->pageable->thumb_image;
                }
            }
        }
    }
    public function frontMenuParent(){
        return $this->belongsTo($this, 'parent_id', 'id');
    }

     public function frontSubMenu(){
         return $this->hasMany($this, 'parent_id', 'id');
     }

}
