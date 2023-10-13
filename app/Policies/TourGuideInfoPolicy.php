<?php

namespace App\Policies;

use App\Models\TourGuideInfo;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TourGuideInfoPolicy
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
        return in_array('viewAny-tour_guide_info', $user->given_permissions) || in_array('view-tour_guide_info', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TourGuideInfo  $tourGuideInfo
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, TourGuideInfo $tourGuideInfo)
    {
        return in_array('viewAny-tour_guide_info', $user->given_permissions) || in_array('view-tour_guide_info', $user->given_permissions) && $tourGuideInfo->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-tour_guide_info', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TourGuideInfo  $tourGuideInfo
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, TourGuideInfo $tourGuideInfo)
    {
        return in_array('updateAny-tour_guide_info', $user->given_permissions) || in_array('update-tour_guide_info', $user->given_permissions) && $tourGuideInfo->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TourGuideInfo  $tourGuideInfo
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, TourGuideInfo $tourGuideInfo)
    {
        return in_array('deleteAny-tour_guide_info', $user->given_permissions) || in_array('delete-tour_guide_info', $user->given_permissions) && $tourGuideInfo->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TourGuideInfo  $tourGuideInfo
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, TourGuideInfo $tourGuideInfo)
    {
        return in_array('restoreAny-tour_guide_info', $user->given_permissions) || in_array('restore-tour_guide_info', $user->given_permissions) && $tourGuideInfo->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TourGuideInfo  $tourGuideInfo
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, TourGuideInfo $tourGuideInfo)
    {
        return in_array('forceDeleteAny-tour_guide_info', $user->given_permissions) || in_array('forceDelete-tour_guide_info', $user->given_permissions) && $tourGuideInfo->creater_id === $user->id;
    }
}
