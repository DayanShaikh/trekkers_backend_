<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumReply extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['forum_topic_id','user_id','content','datetime_added','status','creater_id'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
        })->when(!in_array("viewAny-forum_reply", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
        });
    }

    public function forumTopic(){
        return $this->belongsTo(ForumTopic::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
