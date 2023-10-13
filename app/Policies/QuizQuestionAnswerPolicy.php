<?php

namespace App\Policies;

use App\Models\QuizQuestionAnswer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuizQuestionAnswerPolicy
{
	use HandlesAuthorization;

	/**
	* Determine whether the user can view any models.
	*
	* @param  \App\Models\User  $user
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function before(User $user)
   {
	 return $user->id === 1 ? true : null;
   }

   /**
	* Determine whether the user can view any models.
	*
	* @param  \App\Models\User  $user
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function viewAny(User $user)
   {
		return in_array('viewAny-quiz_question_answer', $user->given_permissions) || in_array('view-quiz_question_answer', $user->given_permissions);
   }

   /**
	* Determine whether the user can view the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\QuizQuestionAnswer  $quiz_question_answer
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function view(User $user, QuizQuestionAnswer $quiz_question_answer)
   {
	   return in_array('viewAny-quiz_question_answer', $user->given_permissions) || in_array('view-quiz_question_answer', $user->given_permissions) && $quiz_question_answer->creater_id === $user->id;
   }

   /**
	* Determine whether the user can create models.
	*
	* @param  \App\Models\User  $user
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function create(User $user)
   {
	   return in_array('create-quiz_question_answer', $user->given_permissions);
   }

   /**
	* Determine whether the user can update the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\QuizQuestionAnswer  $quiz_question_answer
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function update(User $user, QuizQuestionAnswer $quiz_question_answer)
   {
	   return in_array('updateAny-quiz_question_answer', $user->given_permissions) || in_array('update-quiz_question_answer', $user->given_permissions) && $quiz_question_answer->creater_id === $user->id;
   }

   /**
	* Determine whether the user can delete the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\QuizQuestionAnswer  $quiz_question_answer
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function delete(User $user, QuizQuestionAnswer $quiz_question_answer)
   {
	   return in_array('deleteAny-quiz_question_answer', $user->given_permissions) || in_array('delete-quiz_question_answer', $user->given_permissions) && $quiz_question_answer->creater_id === $user->id;
   }

   /**
	* Determine whether the user can restore the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\QuizQuestionAnswer  $quiz_question_answer
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function restore(User $user, QuizQuestionAnswer $quiz_question_answer)
   {
	   return in_array('restoreAny-quiz_question_answer', $user->given_permissions) || in_array('restore-quiz_question_answer', $user->given_permissions) && $quiz_question_answer->creater_id === $user->id;
   }

   /**
	* Determine whether the user can permanently delete the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\QuizQuestionAnswer  $quiz_question_answer
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function forceDelete(User $user, QuizQuestionAnswer $quiz_question_answer)
   {
	   return in_array('forceDeleteAny-quiz_question_answer', $user->given_permissions) || in_array('forceDelete-quiz_question_answer', $user->given_permissions) && $quiz_question_answer->creater_id === $user->id;
   }
}
