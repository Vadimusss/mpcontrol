<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkSpace;
use Illuminate\Auth\Access\Response;

class WorkSpacePolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WorkSpace $workSpace): bool
    {
        return $workSpace->creator()->is($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WorkSpace $workSpace): bool
    {
        return $workSpace->creator()->is($user);
    }
}
