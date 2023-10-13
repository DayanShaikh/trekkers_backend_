<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizQuestion extends Model
{
	use HasFactory, SoftDeletes;
    protected $fillable = ['lesson_id','question','description','is_multiple','sortorder','creater_id'];

    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
            $query->where(function ($query) use ($search) {
				$query->where('question', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-quiz_question", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
            $query->where("creater_id", auth()->user()->id);
    	});
    }

	public function lesson(){
        return $this->belongsTo(Lesson::class);
    }

	public function quizQuestionOptions()
    {
        return $this->hasMany(QuizQuestionOption::class);
    }
}
