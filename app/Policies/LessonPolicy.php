<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LessonPolicy
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
		return in_array('viewAny-lesson', $user->given_permissions) || in_array('view-lesson', $user->given_permissions);
   }

   /**
	* Determine whether the user can view the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\Lesson  $lesson
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function view(User $user, Lesson $lesson)
   {
	   return in_array('viewAny-lesson', $user->given_permissions) || in_array('view-lesson', $user->given_permissions) && $lesson->creater_id === $user->id;
   }

   /**
	* Determine whether the user can create models.
	*
	* @param  \App\Models\User  $user
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function create(User $user)
   {
	   return in_array('create-lesson', $user->given_permissions);
   }

   /**
	* Determine whether the user can update the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\Lesson  $lesson
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function update(User $user, Lesson $lesson)
   {
	   return in_array('updateAny-lesson', $user->given_permissions) || in_array('update-lesson', $user->given_permissions) && $lesson->creater_id === $user->id;
   }

   /**
	* Determine whether the user can delete the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\Lesson  $lesson
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function delete(User $user, Lesson $lesson)
   {
	   return in_array('deleteAny-lesson', $user->given_permissions) || in_array('delete-lesson', $user->given_permissions) && $lesson->creater_id === $user->id;
   }

   /**
	* Determine whether the user can restore the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\Lesson  $lesson
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function restore(User $user, Lesson $lesson)
   {
	   return in_array('restoreAny-lesson', $user->given_permissions) || in_array('restore-lesson', $user->given_permissions) && $lesson->creater_id === $user->id;
   }

   /**
	* Determine whether the user can permanently delete the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\Lesson  $lesson
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function forceDelete(User $user, Lesson $lesson)
   {
	   return in_array('forceDeleteAny-lesson', $user->given_permissions) || in_array('forceDelete-lesson', $user->given_permissions) && $lesson->creater_id === $user->id;
   }
}
