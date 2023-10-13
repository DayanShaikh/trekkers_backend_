<?php

namespace App\Policies;

use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuizQuestionPolicy
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
		return in_array('viewAny-quiz_question', $user->given_permissions) || in_array('view-quiz_question', $user->given_permissions);
   }

   /**
	* Determine whether the user can view the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\QuizQuestion  $quiz_question
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function view(User $user, QuizQuestion $quiz_question)
   {
	   return in_array('viewAny-quiz_question', $user->given_permissions) || in_array('view-quiz_question', $user->given_permissions) && $quiz_question->creater_id === $user->id;
   }

   /**
	* Determine whether the user can create models.
	*
	* @param  \App\Models\User  $user
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function create(User $user)
   {
	   return in_array('create-quiz_question', $user->given_permissions);
   }

   /**
	* Determine whether the user can update the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\QuizQuestion  $quiz_question
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function update(User $user, QuizQuestion $quiz_question)
   {
	   return in_array('updateAny-quiz_question', $user->given_permissions) || in_array('update-quiz_question', $user->given_permissions) && $quiz_question->creater_id === $user->id;
   }

   /**
	* Determine whether the user can delete the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\QuizQuestion  $quiz_question
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function delete(User $user, QuizQuestion $quiz_question)
   {
	   return in_array('deleteAny-quiz_question', $user->given_permissions) || in_array('delete-quiz_question', $user->given_permissions) && $quiz_question->creater_id === $user->id;
   }

   /**
	* Determine whether the user can restore the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\QuizQuestion  $quiz_question
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function restore(User $user, QuizQuestion $quiz_question)
   {
	   return in_array('restoreAny-quiz_question', $user->given_permissions) || in_array('restore-quiz_question', $user->given_permissions) && $quiz_question->creater_id === $user->id;
   }

   /**
	* Determine whether the user can permanently delete the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\QuizQuestion  $quiz_question
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function forceDelete(User $user, QuizQuestion $quiz_question)
   {
	   return in_array('forceDeleteAny-quiz_question', $user->given_permissions) || in_array('forceDelete-quiz_question', $user->given_permissions) && $quiz_question->creater_id === $user->id;
   }
}
