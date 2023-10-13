<?php

namespace App\Policies;

use App\Models\PageGallery;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PageGalleryPolicy
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
        return in_array('viewAny-page_gallery', $user->given_permissions) || in_array('view-page_gallery', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PageGallery  $pageGallery
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, PageGallery $pageGallery)
    {
        return in_array('viewAny-page_gallery', $user->given_permissions) || in_array('view-page_gallery', $user->given_permissions) && $pageGallery->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-page_gallery', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PageGallery  $pageGallery
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, PageGallery $pageGallery)
    {
        return in_array('updateAny-page_gallery', $user->given_permissions) || in_array('update-page_gallery', $user->given_permissions) && $pageGallery->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PageGallery  $pageGallery
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, PageGallery $pageGallery)
    {
        return in_array('deleteAny-page_gallery', $user->given_permissions) || in_array('delete-page_gallery', $user->given_permissions) && $pageGallery->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PageGallery  $pageGallery
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, PageGallery $pageGallery)
    {
        return in_array('restoreAny-page_gallery', $user->given_permissions) || in_array('restore-page_gallery', $user->given_permissions) && $pageGallery->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PageGallery  $pageGallery
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, PageGallery $pageGallery)
    {
        return in_array('forceDeleteAny-page_gallery', $user->given_permissions) || in_array('forceDelete-page_gallery', $user->given_permissions) && $pageGallery->creater_id === $user->id;
    }
}
