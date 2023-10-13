<?php

namespace App\Policies;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmailTemplatePolicy
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
        return in_array('viewAny-email_template', $user->given_permissions) || in_array('view-email_template', $user->given_permissions);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, EmailTemplate $emailTemplate)
    {
        return in_array('viewAny-email_template', $user->given_permissions) || in_array('view-email_template', $user->given_permissions) && $emailTemplate->creater_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return in_array('create-email_template', $user->given_permissions);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, EmailTemplate $emailTemplate)
    {
        return in_array('updateAny-email_template', $user->given_permissions) || in_array('update-email_template', $user->given_permissions) && $emailTemplate->creater_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, EmailTemplate $emailTemplate)
    {
        return $emailTemplate->id !== 1 && (in_array('deleteAny-email_template', $user->given_permissions) || in_array('delete-email_template', $user->given_permissions)) && $emailTemplate->creater_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, EmailTemplate $emailTemplate)
    {
        return in_array('restoreAny-email_template', $user->given_permissions) || in_array('restore-email_template', $user->given_permissions) && $emailTemplate->creater_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EmailTemplate  $emailTemplate
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, EmailTemplate $emailTemplate)
    {
        return in_array('forceDeleteAny-email_template', $user->given_permissions) || in_array('forceDelete-email_template', $user->given_permissions) && $emailTemplate->creater_id === $user->id;
    }
}
