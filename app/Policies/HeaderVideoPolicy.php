<?php

namespace App\Policies;

use App\Models\HeaderVideo;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HeaderVideoPolicy
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
         return in_array('viewAny-header_video', $user->given_permissions) || in_array('view-header_video', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\HeaderVideo  $headerVideo
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, HeaderVideo $headerVideo)
    {
        return in_array('viewAny-header_video', $user->given_permissions) || in_array('view-header_video', $user->given_permissions) && $headerVideo->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-header_video', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\HeaderVideo  $headerVideo
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, HeaderVideo $headerVideo)
    {
        return in_array('updateAny-header_video', $user->given_permissions) || in_array('update-header_video', $user->given_permissions) && $headerVideo->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\HeaderVideo  $headerVideo
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, HeaderVideo $headerVideo)
    {
        return in_array('deleteAny-header_video', $user->given_permissions) || in_array('delete-header_video', $user->given_permissions) && $headerVideo->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\HeaderVideo  $headerVideo
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, HeaderVideo $headerVideo)
    {
        return in_array('restoreAny-header_video', $user->given_permissions) || in_array('restore-header_video', $user->given_permissions) && $headerVideo->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\HeaderVideo  $headerVideo
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, HeaderVideo $headerVideo)
    {
        return in_array('forceDeleteAny-header_video', $user->given_permissions) || in_array('forceDelete-header_video', $user->given_permissions) && $headerVideo->creater_id === $user->id;
    }
}
