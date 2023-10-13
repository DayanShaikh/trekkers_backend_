<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
	use HasFactory, SoftDeletes;
    protected $fillable = ['course_id','title','small_description','details','duration','intro_video','creater_id'];

    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('title', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-lesson", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
    	});
    }

	public function course(){
        return $this->belongsTo(Course::class);
    }
	
	public function quizQuestions()
    {
        return $this->hasMany(QuizQuestion::class);
    }
}
