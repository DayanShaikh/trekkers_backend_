<?php

namespace App\Policies;

use App\Models\Airline;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AirlinePolicy
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
         return in_array('viewAny-airline', $user->given_permissions) || in_array('view-airline', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Airline  $airline
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Airline $airline)
    {
        return in_array('viewAny-airline', $user->given_permissions) || in_array('view-airline', $user->given_permissions) && $airline->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-airline', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Airline  $airline
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Airline $airline)
    {
        return in_array('updateAny-airline', $user->given_permissions) || in_array('update-airline', $user->given_permissions) && $airline->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Airline  $airline
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Airline $airline)
    {
        return in_array('deleteAny-airline', $user->given_permissions) || in_array('delete-airline', $user->given_permissions) && $airline->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Airline  $airline
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Airline $airline)
    {
        return in_array('restoreAny-airline', $user->given_permissions) || in_array('restore-airline', $user->given_permissions) && $airline->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Airline  $airline
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Airline $airline)
    {
        return in_array('forceDeleteAny-airline', $user->given_permissions) || in_array('forceDelete-airline', $user->given_permissions) && $airline->creater_id === $user->id;
    }
}
