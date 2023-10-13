<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizQuestionAnswer extends Model
{
	use HasFactory, SoftDeletes;
    protected $fillable = ['quiz_question_id','quiz_question_option_id','status','creater_id'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
			$query->where(function ($query) use ($search) {
				$query->where('quiz_question_id', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-quiz_question_answer", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
                $query->where("creater_id", auth()->user()->id);
        });
    }

	public function quizQuestionOption()
    {
        return $this->belongsTo(QuizQuestionOption::class);
    }
}
