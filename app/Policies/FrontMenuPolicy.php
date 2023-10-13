<?php

namespace App\Policies;

use App\Models\FrontMenu;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FrontMenuPolicy
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
         return in_array('viewAny-front_menu', $user->given_permissions) || in_array('view-front_menu', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FrontMenu  $frontMenu
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, FrontMenu $frontMenu)
    {
        return in_array('viewAny-front_menu', $user->given_permissions) || in_array('view-front_menu', $user->given_permissions) && $frontMenu->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-front_menu', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FrontMenu  $frontMenu
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, FrontMenu $frontMenu)
    {
        return in_array('updateAny-front_menu', $user->given_permissions) || in_array('update-front_menu', $user->given_permissions) && $frontMenu->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FrontMenu  $frontMenu
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, FrontMenu $frontMenu)
    {
        return in_array('deleteAny-front_menu', $user->given_permissions) || in_array('delete-front_menu', $user->given_permissions) && $frontMenu->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FrontMenu  $frontMenu
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, FrontMenu $frontMenu)
    {
        return in_array('restoreAny-front_menu', $user->given_permissions) || in_array('restore-front_menu', $user->given_permissions) && $frontMenu->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FrontMenu  $frontMenu
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, FrontMenu $frontMenu)
    {
        return in_array('forceDeleteAny-front_menu', $user->given_permissions) || in_array('forceDelete-front_menu', $user->given_permissions) && $frontMenu->creater_id === $user->id;
    }
}
