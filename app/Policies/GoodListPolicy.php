<?php

namespace App\Policies;

use App\Models\GoodList;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GoodListPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GoodList $goodList): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GoodList $goodList): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GoodList $goodList): bool
    {
        return $goodList->creator()->is($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, GoodList $goodList): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, GoodList $goodList): bool
    {
        //
    }
}
