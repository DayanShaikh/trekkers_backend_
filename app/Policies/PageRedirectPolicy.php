<?php

namespace App\Policies;

use App\Models\PageRedirect;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PageRedirectPolicy
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
		return in_array('viewAny-page_redirect', $user->given_permissions) || in_array('view-page_redirect', $user->given_permissions);
   }

   /**
	* Determine whether the user can view the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\PageRedirect  $page_redirect
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function view(User $user, PageRedirect $page_redirect)
   {
	   return in_array('viewAny-page_redirect', $user->given_permissions) || in_array('view-page_redirect', $user->given_permissions) && $page_redirect->creater_id === $user->id;
   }

   /**
	* Determine whether the user can create models.
	*
	* @param  \App\Models\User  $user
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function create(User $user)
   {
	   return in_array('create-page_redirect', $user->given_permissions);
   }

   /**
	* Determine whether the user can update the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\PageRedirect  $page_redirect
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function update(User $user, PageRedirect $page_redirect)
   {
	   return in_array('updateAny-page_redirect', $user->given_permissions) || in_array('update-page_redirect', $user->given_permissions) && $page_redirect->creater_id === $user->id;
   }

   /**
	* Determine whether the user can delete the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\PageRedirect  $page_redirect
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function delete(User $user, PageRedirect $page_redirect)
   {
	   return in_array('deleteAny-page_redirect', $user->given_permissions) || in_array('delete-page_redirect', $user->given_permissions) && $page_redirect->creater_id === $user->id;
   }

   /**
	* Determine whether the user can restore the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\PageRedirect  $page_redirect
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function restore(User $user, PageRedirect $page_redirect)
   {
	   return in_array('restoreAny-page_redirect', $user->given_permissions) || in_array('restore-page_redirect', $user->given_permissions) && $page_redirect->creater_id === $user->id;
   }

   /**
	* Determine whether the user can permanently delete the model.
	*
	* @param  \App\Models\User  $user
	* @param  \App\Models\PageRedirect  $page_redirect
	* @return \Illuminate\Auth\Access\Response|bool
	*/
   public function forceDelete(User $user, PageRedirect $page_redirect)
   {
	   return in_array('forceDeleteAny-page_redirect', $user->given_permissions) || in_array('forceDelete-page_redirect', $user->given_permissions) && $page_redirect->creater_id === $user->id;
   }
}
