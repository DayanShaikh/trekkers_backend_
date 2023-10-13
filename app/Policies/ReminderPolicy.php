<?php

namespace App\Policies;

use App\Models\Reminder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReminderPolicy
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
        return in_array('viewAny-reminder', $user->given_permissions) || in_array('view-reminder', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Reminder $reminder)
    {
        return in_array('viewAny-reminder', $user->given_permissions) || in_array('view-reminder', $user->given_permissions) && $reminder->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-reminder', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Reminder $reminder)
    {
        return in_array('updateAny-reminder', $user->given_permissions) || in_array('update-reminder', $user->given_permissions) && $reminder->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Reminder $reminder)
    {
        return in_array('deleteAny-reminder', $user->given_permissions) || in_array('delete-reminder', $user->given_permissions) && $reminder->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Reminder $reminder)
    {
        return in_array('restoreAny-reminder', $user->given_permissions) || in_array('restore-reminder', $user->given_permissions) && $reminder->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Reminder  $reminder
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Reminder $reminder)
    {
        return in_array('forceDeleteAny-reminder', $user->given_permissions) || in_array('forceDelete-reminder', $user->given_permissions) && $reminder->creater_id === $user->id;
    }
}
