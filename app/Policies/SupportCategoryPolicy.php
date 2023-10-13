<?php

namespace App\Policies;

use App\Models\SupportCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupportCategoryPolicy
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
       return in_array('viewAny-support_category', $user->given_permissions) || in_array('view-support_category', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SupportCategory  $supportCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, SupportCategory $supportCategory)
    {
        return in_array('viewAny-support_category', $user->given_permissions) || in_array('view-support_category', $user->given_permissions) && $supportCategory->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-support_category', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SupportCategory  $supportCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, SupportCategory $supportCategory)
    {
        return in_array('updateAny-support_category', $user->given_permissions) || in_array('update-support_category', $user->given_permissions) && $supportCategory->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SupportCategory  $supportCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, SupportCategory $supportCategory)
    {
        return in_array('deleteAny-support_category', $user->given_permissions) || in_array('delete-support_category', $user->given_permissions) && $supportCategory->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SupportCategory  $supportCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, SupportCategory $supportCategory)
    {
        return in_array('restoreAny-support_category', $user->given_permissions) || in_array('restore-support_category', $user->given_permissions) && $supportCategory->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SupportCategory  $supportCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, SupportCategory $supportCategory)
    {
        return in_array('forceDeleteAny-support_category', $user->given_permissions) || in_array('forceDelete-support_category', $user->given_permissions) && $supportCategory->creater_id === $user->id;
    }
}
