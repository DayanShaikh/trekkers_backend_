<?php

namespace App\Policies;

use App\Models\TripTicketUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TripTicketUserPolicy
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
        return in_array('viewAny-trip_ticket_user', $user->given_permissions) || in_array('view-trip_ticket_user', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TripTicketUser  $tripTicketUser
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, TripTicketUser $tripTicketUser)
    {
        return in_array('viewAny-trip_ticket_user', $user->given_permissions) || in_array('view-trip_ticket_user', $user->given_permissions) && $tripTicketUser->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-trip_ticket_user', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TripTicketUser  $tripTicketUser
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, TripTicketUser $tripTicketUser)
    {
        return in_array('updateAny-trip_ticket_user', $user->given_permissions) || in_array('update-trip_ticket_user', $user->given_permissions) && $tripTicketUser->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TripTicketUser  $tripTicketUser
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, TripTicketUser $tripTicketUser)
    {
        return in_array('deleteAny-trip_ticket_user', $user->given_permissions) || in_array('delete-trip_ticket_user', $user->given_permissions) && $tripTicketUser->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TripTicketUser  $tripTicketUser
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, TripTicketUser $tripTicketUser)
    {
        return in_array('restoreAny-trip_ticket_user', $user->given_permissions) || in_array('restore-trip_ticket_user', $user->given_permissions) && $tripTicketUser->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TripTicketUser  $tripTicketUser
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, TripTicketUser $tripTicketUser)
    {
        return in_array('forceDeleteAny-trip_ticket_user', $user->given_permissions) || in_array('forceDelete-trip_ticket_user', $user->given_permissions) && $tripTicketUser->creater_id === $user->id;
    }
}
