<?php

namespace App\Policies;

use App\Models\Discount;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DiscountPolicy
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
        return in_array('viewAny-discount', $user->given_permissions) || in_array('view-discount', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Discount $discount)
    {
        return in_array('viewAny-discount', $user->given_permissions) || in_array('view-discount', $user->given_permissions) && $discount->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-discount', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Discount $discount)
    {
        return in_array('updateAny-discount', $user->given_permissions) || in_array('update-discount', $user->given_permissions) && $discount->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Discount $discount)
    {
        return in_array('deleteAny-discount', $user->given_permissions) || in_array('delete-discount', $user->given_permissions) && $discount->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Discount $discount)
    {
        return in_array('restoreAny-discount', $user->given_permissions) || in_array('restore-discount', $user->given_permissions) && $discount->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Discount $discount)
    {
        return in_array('forceDeleteAny-discount', $user->given_permissions) || in_array('forceDelete-discount', $user->given_permissions) && $discount->creater_id === $user->id;
    }
}
