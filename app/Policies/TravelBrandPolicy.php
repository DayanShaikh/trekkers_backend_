<?php

namespace App\Policies;

use App\Models\TravelBrand;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TravelBrandPolicy
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
        return in_array('viewAny-travel_brand', $user->given_permissions) || in_array('view-travel_brand', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelBrand  $travelBrand
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, TravelBrand $travelBrand)
    {
        return in_array('viewAny-travel_brand', $user->given_permissions) || in_array('view-travel_brand', $user->given_permissions) && $travelBrand->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-travel_brand', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelBrand  $travelBrand
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, TravelBrand $travelBrand)
    {
        return in_array('updateAny-travel_brand', $user->given_permissions) || in_array('update-travel_brand', $user->given_permissions) && $travelBrand->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelBrand  $travelBrand
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, TravelBrand $travelBrand)
    {
        return in_array('deleteAny-travel_brand', $user->given_permissions) || in_array('delete-travel_brand', $user->given_permissions) && $travelBrand->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelBrand  $travelBrand
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, TravelBrand $travelBrand)
    {
        return in_array('restoreAny-travel_brand', $user->given_permissions) || in_array('restore-travel_brand', $user->given_permissions) && $travelBrand->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TravelBrand  $travelBrand
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, TravelBrand $travelBrand)
    {
        return in_array('forceDeleteAny-travel_brand', $user->given_permissions) || in_array('forceDelete-travel_brand', $user->given_permissions) && $travelBrand->creater_id === $user->id;
    }
}
