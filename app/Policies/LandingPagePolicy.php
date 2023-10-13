<?php

namespace App\Policies;

use App\Models\LandingPage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LandingPagePolicy
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
        return in_array('viewAny-landing_page', $user->given_permissions) || in_array('view-landing_page', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LandingPage  $landingPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, LandingPage $landingPage)
    {
        return in_array('viewAny-landing_page', $user->given_permissions) || in_array('view-landing_page', $user->given_permissions) && $landingPage->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-landing_page', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LandingPage  $landingPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, LandingPage $landingPage)
    {
        return in_array('updateAny-landing_page', $user->given_permissions) || in_array('update-landing_page', $user->given_permissions) && $landingPage->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LandingPage  $landingPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, LandingPage $landingPage)
    {
        return in_array('deleteAny-landing_page', $user->given_permissions) || in_array('delete-landing_page', $user->given_permissions) && $landingPage->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LandingPage  $landingPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, LandingPage $landingPage)
    {
        return in_array('restoreAny-landing_page', $user->given_permissions) || in_array('restore-landing_page', $user->given_permissions) && $landingPage->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LandingPage  $landingPage
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, LandingPage $landingPage)
    {
        return in_array('forceDeleteAny-landing_page', $user->given_permissions) || in_array('forceDelete-landing_page', $user->given_permissions) && $landingPage->creater_id === $user->id;
    }
}
