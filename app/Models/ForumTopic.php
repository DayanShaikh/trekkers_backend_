<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumTopic extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['forum_category_id','user_id','title','content','announcement','view','status','creater_id'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('title', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-forum_topic", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
        });
    }
    public function forumCategory(){
        return $this->belongsTo(ForumCategory::class);
    }
	
	public function forumReplies(){
        return $this->hasMany(ForumReply::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

}
