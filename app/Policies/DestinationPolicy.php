<?php

namespace App\Policies;

use App\Models\Destination;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DestinationPolicy
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
        return in_array('viewAny-destination', $user->given_permissions) || in_array('view-destination', $user->given_permissions);
    }
    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Destination  $destination
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Destination $destination)
    {
        return in_array('viewAny-destination', $user->given_permissions) || in_array('view-destination', $user->given_permissions) && $destination->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-destination', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Destination  $destination
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Destination $destination)
    {
        return in_array('updateAny-destination', $user->given_permissions) || in_array('update-destination', $user->given_permissions) && $destination->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Destination  $destination
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Destination $destination)
    {
        return in_array('deleteAny-destination', $user->given_permissions) || in_array('delete-destination', $user->given_permissions) && $destination->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Destination  $destination
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Destination $destination)
    {
        return in_array('restoreAny-destination', $user->given_permissions) || in_array('restore-destination', $user->given_permissions) && $destination->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Destination  $destination
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Destination $destination)
    {
        return in_array('forceDeleteAny-destination', $user->given_permissions) || in_array('forceDelete-destination', $user->given_permissions) && $destination->creater_id === $user->id;
    }
}
