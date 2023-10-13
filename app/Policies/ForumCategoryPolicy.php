<?php

namespace App\Policies;

use App\Models\ForumCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ForumCategoryPolicy
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
        return in_array('viewAny-forum_category', $user->given_permissions) || in_array('view-forum_category', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ForumCategory  $forumCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, ForumCategory $forumCategory)
    {
        return in_array('viewAny-forum_category', $user->given_permissions) || in_array('view-forum_category', $user->given_permissions) && $forumCategory->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-forum_category', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ForumCategory  $forumCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, ForumCategory $forumCategory)
    {
        return in_array('updateAny-forum_category', $user->given_permissions) || in_array('update-forum_category', $user->given_permissions) && $forumCategory->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ForumCategory  $forumCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, ForumCategory $forumCategory)
    {
        return in_array('deleteAny-forum_category', $user->given_permissions) || in_array('delete-forum_category', $user->given_permissions) && $forumCategory->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ForumCategory  $forumCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, ForumCategory $forumCategory)
    {
        return in_array('restoreAny-forum_category', $user->given_permissions) || in_array('restore-forum_category', $user->given_permissions) && $forumCategory->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ForumCategory  $forumCategory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, ForumCategory $forumCategory)
    {
        return in_array('forceDeleteAny-forum_category', $user->given_permissions) || in_array('forceDelete-forum_category', $user->given_permissions) && $forumCategory->creater_id === $user->id;
    }
}
