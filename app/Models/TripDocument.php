<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class TripDocument extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['trip_id','title','document_url','sortorder','status', 'creater_id'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('title', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-trip_document", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
        });
    }

    protected $appends = ['document_url_url'];

    public function getDocumentUrlUrlAttribute()
	{
		if(!empty($this->document_url) ){
			return url(Storage::url($this->document_url));
		}
		else{
			return $this->document_url;
		}
	}

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
