<?php

namespace App\Policies;

use App\Models\AgeGroup;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AgeGroupPolicy
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
        return in_array('viewAny-age_group', $user->given_permissions) || in_array('view-age_group', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AgeGroup  $ageGroup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, AgeGroup $ageGroup)
    {
        return in_array('viewAny-age_group', $user->given_permissions) || in_array('view-age_group', $user->given_permissions) && $ageGroup->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-age_group', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AgeGroup  $ageGroup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, AgeGroup $ageGroup)
    {
        return in_array('updateAny-age_group', $user->given_permissions) || in_array('update-age_group', $user->given_permissions) && $ageGroup->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AgeGroup  $ageGroup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, AgeGroup $ageGroup)
    {
        return in_array('deleteAny-age_group', $user->given_permissions) || in_array('delete-age_group', $user->given_permissions) && $ageGroup->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AgeGroup  $ageGroup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, AgeGroup $ageGroup)
    {
        return in_array('restoreAny-age_group', $user->given_permissions) || in_array('restore-age_group', $user->given_permissions) && $ageGroup->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AgeGroup  $ageGroup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, AgeGroup $ageGroup)
    {
        return in_array('forceDeleteAny-age_group', $user->given_permissions) || in_array('forceDelete-age_group', $user->given_permissions) && $ageGroup->creater_id === $user->id;
    }
}
