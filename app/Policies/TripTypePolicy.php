<?php

namespace App\Policies;

use App\Models\TripType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TripTypePolicy
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
         return in_array('viewAny-trip_type', $user->given_permissions) || in_array('view-trip_type', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TripType  $tripType
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, TripType $tripType)
    {
        return in_array('viewAny-trip_type', $user->given_permissions) || in_array('view-trip_type', $user->given_permissions) && $tripType->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-trip_type', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TripType  $tripType
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, TripType $tripType)
    {
        return in_array('updateAny-trip_type', $user->given_permissions) || in_array('update-trip_type', $user->given_permissions) && $tripType->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TripType  $tripType
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, TripType $tripType)
    {
        return in_array('deleteAny-trip_type', $user->given_permissions) || in_array('delete-trip_type', $user->given_permissions) && $tripType->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TripType  $tripType
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, TripType $tripType)
    {
        return in_array('restoreAny-trip_type', $user->given_permissions) || in_array('restore-trip_type', $user->given_permissions) && $tripType->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TripType  $tripType
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, TripType $tripType)
    {
        return in_array('forceDeleteAny-trip_type', $user->given_permissions) || in_array('forceDelete-trip_type', $user->given_permissions) && $tripType->creater_id === $user->id;
    }
}
