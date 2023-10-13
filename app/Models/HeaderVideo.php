<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;

class HeaderVideo extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['title','webm_format','mp4_format','status', 'creater_id'];

    protected $appends = ['webm_format_url', 'mp4_format_url'];

    public function getWebmFormatUrlAttribute()
	{
			//return url(Storage::url($this->webm_format));
		if( !empty($this->webm_format) ){
			return config("filesystems.default")=='s3' ? url(Storage::disk("s3")->temporaryUrl($this->webm_format, now()->addMinutes(120))) : url(Storage::url($this->webm_format));
		}
		else{
			return $this->webm_format;
		}
	}

    public function getMp4FormatUrlAttribute()
	{
		if(!empty($this->mp4_format) ){
			//return url(Storage::url($this->mp4_format));
			return config("filesystems.default")=='s3' ? url(Storage::disk("s3")->temporaryUrl($this->mp4_format, now()->addMinutes(120))) : url(Storage::url($this->mp4_format));
		}
		else{
			return $this->mp4_format;
		}
	}

    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('title', 'like', '%'.$search.'%');
            });
		})->when(!in_array("viewAny-header_video", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
			$query->where("creater_id", auth()->user()->id);
	});
    }
}
