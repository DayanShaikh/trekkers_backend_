<?php

namespace App\Policies;

use App\Models\AgeGroupMonthMeta;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AgeGroupMonthMetaPolicy
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
          return in_array('viewAny-age_group_month_meta', $user->given_permissions) || in_array('view-age_group_month_meta', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AgeGroupMonthMeta  $ageGroupMonthMeta
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, AgeGroupMonthMeta $ageGroupMonthMeta)
    {
        return in_array('viewAny-age_group_month_meta', $user->given_permissions) || in_array('view-age_group_month_meta', $user->given_permissions) && $ageGroupMonthMeta->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AgeGroupMonthMeta  $ageGroupMonthMeta
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, AgeGroupMonthMeta $ageGroupMonthMeta)
    {
        return in_array('updateAny-age_group_month_meta', $user->given_permissions) || in_array('update-age_group_month_meta', $user->given_permissions) && $ageGroupMonthMeta->creater === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AgeGroupMonthMeta  $ageGroupMonthMeta
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, AgeGroupMonthMeta $ageGroupMonthMeta)
    {
        return in_array('deleteAny-age_group_month_meta', $user->given_permissions) || in_array('delete-age_group_month_meta', $user->given_permissions) && $ageGroupMonthMeta->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AgeGroupMonthMeta  $ageGroupMonthMeta
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, AgeGroupMonthMeta $ageGroupMonthMeta)
    {
        return in_array('restoreAny-age_group_month_meta', $user->given_permissions) || in_array('restore-age_group_month_meta', $user->given_permissions) && $ageGroupMonthMeta->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AgeGroupMonthMeta  $ageGroupMonthMeta
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, AgeGroupMonthMeta $ageGroupMonthMeta)
    {
        return in_array('forceDeleteAny-age_group_month_meta', $user->given_permissions) || in_array('forceDelete-age_group_month_meta', $user->given_permissions) && $ageGroupMonthMeta->creater_id === $user->id;
    }
}
