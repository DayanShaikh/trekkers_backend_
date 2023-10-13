<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfigPage extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable =['title','show_in_menu','sortorder','status','creater_id'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('title', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-config_page", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
       });
    }

    public function getDir(){
        $dir = public_path() . "/public/config";
        return $dir;
    }

    public function configVariables()
    {
        return $this->hasMany(ConfigVariable::class);
    }
}
