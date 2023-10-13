<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Upload extends Model
{
    use HasFactory;
    protected $fillable = ['file_name','file_location','creater_id'];
	protected $appends = ['file_url'];
    public function getFileUrlAttribute()
	{
		if(!empty($this->file_location) ){
			return url(Storage::url($this->file_location));
		}
		else{
			return $this->file_location;
		}
	}

	public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('file_name', 'like', '%'.$search.'%');
            });
        });
    }
}
