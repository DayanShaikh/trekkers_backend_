<?php

namespace App\Policies;

use App\Models\Location;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LocationPolicy
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
         return in_array('viewAny-location', $user->given_permissions) || in_array('view-location', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Location $location)
    {
        return in_array('viewAny-location', $user->given_permissions) || in_array('view-location', $user->given_permissions) && $location->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-location', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Location $location)
    {
        return in_array('updateAny-location', $user->given_permissions) || in_array('update-location', $user->given_permissions) && $location->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Location $location)
    {
        return in_array('deleteAny-location', $user->given_permissions) || in_array('delete-location', $user->given_permissions) && $location->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Location $location)
    {
        return in_array('restoreAny-location', $user->given_permissions) || in_array('restore-location', $user->given_permissions) && $location->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Location $location)
    {
        return in_array('forceDeleteAny-location', $user->given_permissions) || in_array('forceDelete-location', $user->given_permissions) && $location->creater_id === $user->id;
    }
}
