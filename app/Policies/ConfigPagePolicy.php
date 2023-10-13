<?php

namespace App\Policies;

use App\Models\ConfigPage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConfigPagePolicy
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
       return in_array('viewAny-config_page', $user->given_permissions) || in_array('view-config_page', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ConfigPage  $configPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, ConfigPage $configPage)
    {
        return in_array('viewAny-config_page', $user->given_permissions) || in_array('view-config_page', $user->given_permissions) && $configPage->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-config_page', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ConfigPage  $configPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, ConfigPage $configPage)
    {
        return in_array('updateAny-config_page', $user->given_permissions) || in_array('update-config_page', $user->given_permissions) && $configPage->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ConfigPage  $configPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, ConfigPage $configPage)
    {
        return in_array('deleteAny-config_page', $user->given_permissions) || in_array('delete-config_page', $user->given_permissions) && $configPage->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ConfigPage  $configPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, ConfigPage $configPage)
    {
        return in_array('restoreAny-config_page', $user->given_permissions) || in_array('restore-config_page', $user->given_permissions) && $configPage->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ConfigPage  $configPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, ConfigPage $configPage)
    {
        return in_array('forceDeleteAny-config_page', $user->given_permissions) || in_array('forceDelete-config_page', $user->given_permissions) && $configPage->creater_id === $user->id;
    }
}
