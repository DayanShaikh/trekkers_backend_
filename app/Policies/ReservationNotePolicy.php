<?php

namespace App\Policies;

use App\Models\ReservationNote;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservationNotePolicy
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
        return in_array('viewAny-reservation_note', $user->given_permissions) || in_array('view-reservation_note', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ReservationNote  $reservationNote
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, ReservationNote $reservationNote)
    {
        return in_array('viewAny-reservation_note', $user->given_permissions) || in_array('view-reservation_note', $user->given_permissions) && $reservationNote->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-reservation_note', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ReservationNote  $reservationNote
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, ReservationNote $reservationNote)
    {
        return in_array('updateAny-reservation_note', $user->given_permissions) || in_array('update-reservation_note', $user->given_permissions) && $reservationNote->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ReservationNote  $reservationNote
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, ReservationNote $reservationNote)
    {
        return in_array('deleteAny-reservation_note', $user->given_permissions) || in_array('delete-reservation_note', $user->given_permissions) && $reservationNote->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ReservationNote  $reservationNote
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, ReservationNote $reservationNote)
    {
        return in_array('restoreAny-reservation_note', $user->given_permissions) || in_array('restore-reservation_note', $user->given_permissions) && $reservationNote->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ReservationNote  $reservationNote
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, ReservationNote $reservationNote)
    {
        return in_array('forceDeleteAny-reservation_note', $user->given_permissions) || in_array('forceDelete-reservation_note', $user->given_permissions) && $reservationNote->creater_id === $user->id;
    }
}
