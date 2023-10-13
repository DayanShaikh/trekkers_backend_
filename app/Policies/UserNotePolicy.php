<?php

namespace App\Policies;

use App\Models\UserNote;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserNotePolicy
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
        return in_array('viewAny-user_note', $user->given_permissions) || in_array('view-user_note', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserNote  $userNote
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, UserNote $userNote)
    {
        return in_array('viewAny-user_note', $user->given_permissions) || in_array('view-user_note', $user->given_permissions) && $userNote->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-user_note', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserNote  $userNote
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, UserNote $userNote)
    {
        return in_array('updateAny-user_note', $user->given_permissions) || in_array('update-user_note', $user->given_permissions) && $userNote->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserNote  $userNote
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, UserNote $userNote)
    {
        return in_array('deleteAny-user_note', $user->given_permissions) || in_array('delete-user_note', $user->given_permissions) && $userNote->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserNote  $userNote
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, UserNote $userNote)
    {
        return in_array('restoreAny-user_note', $user->given_permissions) || in_array('restore-user_note', $user->given_permissions) && $userNote->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserNote  $userNote
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, UserNote $userNote)
    {
        return in_array('forceDeleteAny-user_note', $user->given_permissions) || in_array('forceDelete-user_note', $user->given_permissions) && $userNote->creater_id === $user->id;
    }
}
