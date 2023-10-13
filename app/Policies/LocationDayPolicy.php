<?php

namespace App\Policies;

use App\Models\LocationDay;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LocationDayPolicy
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
        return in_array('viewAny-location_day', $user->given_permissions) || in_array('view-location_day', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LocationDay  $locationDay
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, LocationDay $locationDay)
    {
        return in_array('viewAny-location_day', $user->given_permissions) || in_array('view-location_day', $user->given_permissions) && $locationDay->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-location_day', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LocationDay  $locationDay
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, LocationDay $locationDay)
    {
        return in_array('updateAny-location_day', $user->given_permissions) || in_array('update-location_day', $user->given_permissions) && $locationDay->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LocationDay  $locationDay
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, LocationDay $locationDay)
    {
        return in_array('deleteAny-location_day', $user->given_permissions) || in_array('delete-location_day', $user->given_permissions) && $locationDay->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LocationDay  $locationDay
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, LocationDay $locationDay)
    {
        return in_array('restoreAny-location_day', $user->given_permissions) || in_array('restore-location_day', $user->given_permissions) && $locationDay->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LocationDay  $locationDay
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, LocationDay $locationDay)
    {
        return in_array('forceDeleteAny-location_day', $user->given_permissions) || in_array('forceDelete-location_day', $user->given_permissions) && $locationDay->creater_id === $user->id;
    }
}
