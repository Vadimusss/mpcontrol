<?php

namespace App\Policies;

use App\Models\GoodList;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GoodListPolicy
{
    public function delete(User $user, GoodList $goodList): bool
    {
        return $goodList->creator()->is($user);
    }
}
