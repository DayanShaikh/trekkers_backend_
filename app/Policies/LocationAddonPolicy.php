<?php

namespace App\Policies;

use App\Models\LocationAddon;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LocationAddonPolicy
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
       return in_array('viewAny-location_addon', $user->given_permissions) || in_array('view-location_addon', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LocationAddon  $locationAddon
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, LocationAddon $locationAddon)
    {
        return in_array('viewAny-location_addon', $user->given_permissions) || in_array('view-location_addon', $user->given_permissions) && $locationAddon->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-location_addon', $user->given_permissions);
    }
    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LocationAddon  $locationAddon
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, LocationAddon $locationAddon)
    {
        return in_array('updateAny-location_addon', $user->given_permissions) || in_array('update-location_addon', $user->given_permissions) && $locationAddon->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LocationAddon  $locationAddon
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, LocationAddon $locationAddon)
    {
        return in_array('deleteAny-location_addon', $user->given_permissions) || in_array('delete-location_addon', $user->given_permissions) && $locationAddon->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LocationAddon  $locationAddon
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, LocationAddon $locationAddon)
    {
        return in_array('restoreAny-location_addon', $user->given_permissions) || in_array('restore-location_addon', $user->given_permissions) && $locationAddon->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LocationAddon  $locationAddon
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, LocationAddon $locationAddon)
    {
        return in_array('forceDeleteAny-location_addon', $user->given_permissions) || in_array('forceDelete-location_addon', $user->given_permissions) && $locationAddon->creater_id === $user->id;
    }
}
