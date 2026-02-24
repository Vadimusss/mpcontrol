<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShopPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Shop $shop): bool
    {
        return $shop->owner->is($user) || $shop->hasAccess($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Shop $shop): bool
    {
        return $shop->owner->is($user) || $shop->isAdmin($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Shop $shop): bool
    {
        return $shop->owner->is($user);
    }

    /**
     * Determine whether the user can manage shop users.
     */
    public function manageUsers(User $user, Shop $shop): bool
    {
        return $shop->owner->is($user) || $shop->isAdmin($user);
    }

    /**
     * Determine whether the user can perform any shop operations.
     */
    public function anyOperation(User $user, Shop $shop): bool
    {
        return $shop->hasAccess($user);
    }
}
