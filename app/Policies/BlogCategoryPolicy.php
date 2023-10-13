<?php

namespace App\Policies;

use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BlogCategoryPolicy
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
        return in_array('viewAny-blog_category', $user->given_permissions) || in_array('view-blog_category', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BlogCategory  $blogCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, BlogCategory $blogCategory)
    {
        return in_array('viewAny-blog_category', $user->given_permissions) || in_array('view-blog_category', $user->given_permissions) && $blogCategory->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-blog_category', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BlogCategory  $blogCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, BlogCategory $blogCategory)
    {
        return in_array('updateAny-blog_category', $user->given_permissions) || in_array('update-blog_category', $user->given_permissions) && $blogCategory->creater_id === $user->id;
    }
    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BlogCategory  $blogCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, BlogCategory $blogCategory)
    {
        return in_array('deleteAny-blog_category', $user->given_permissions) || in_array('delete-blog_category', $user->given_permissions) && $blogCategory->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BlogCategory  $blogCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, BlogCategory $blogCategory)
    {
        return in_array('restoreAny-blog_category', $user->given_permissions) || in_array('restore-blog_category', $user->given_permissions) && $blogCategory->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BlogCategory  $blogCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, BlogCategory $blogCategory)
    {
        return in_array('forceDeleteAny-blog_category', $user->given_permissions) || in_array('forceDelete-blog_category', $user->given_permissions) && $blogCategory->creater_id === $user->id;
    }
}
