<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ConfigVariable extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable=['config_page_id','input_type','name','notes','options','config_key',
    'value','autoload', 'sortorder','status', 'creater_id'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('name', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-config_variable", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
       });
    }

	protected $appends = ['image_url'];
    public function getImageUrlAttribute(){
        if( !empty($this->value) ){
            if(strpos($this->value,'public/config') !== -1){
                return url(Storage::url($this->value));
            }
        }
    }
    public function configPage()
    {
        return $this->belongsTo(ConfigPage::class);
    }
}
