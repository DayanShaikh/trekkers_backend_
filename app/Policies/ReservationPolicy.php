<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservationPolicy
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
        return in_array('viewAny-reservation', $user->given_permissions) || in_array('view-reservation', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Reservation $reservation)
    {
        return in_array('viewAny-reservation', $user->given_permissions) || in_array('view-reservation', $user->given_permissions) && $reservation->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-reservation', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Reservation $reservation)
    {
        return in_array('updateAny-reservation', $user->given_permissions) || in_array('update-reservation', $user->given_permissions) && $reservation->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Reservation $reservation)
    {
        return in_array('deleteAny-reservation', $user->given_permissions) || in_array('delete-reservation', $user->given_permissions) && $reservation->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Reservation $reservation)
    {
        return in_array('restoreAny-reservation', $user->given_permissions) || in_array('restore-reservation', $user->given_permissions) && $reservation->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Reservation $reservation)
    {
        return in_array('forceDeleteAny-reservation', $user->given_permissions) || in_array('forceDelete-reservation', $user->given_permissions) && $reservation->creater_id === $user->id;
    }
}
