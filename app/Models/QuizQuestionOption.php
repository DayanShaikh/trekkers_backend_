<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizQuestionOption extends Model
{
	use HasFactory, SoftDeletes;
    protected $fillable = ['quiz_question_id','option','is_correct'];
    public function scopeFilter($query,array $filters)
    {
        $query->when($filters['search']?? null,function($query,$search){
			$query->where(function ($query) use ($search) {
				$query->where('option', 'like', '%'.$search.'%');
            });
        })->when(!in_array("viewAny-quiz_question_option", auth()->user()->given_permissions) && auth()->user()->id !== 1, function($query){
                $query->where("creater_id", auth()->user()->id);
        });
    }

	public function quizQuestion()
	{
		return $this->belongsToMany(QuizQuestion::class);
	}
}
