<?php

namespace App\Listeners;

use App\Events\ShopDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteShopData
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ShopDeleted $event): void
    {
        $event->shop->apiKey()->delete();
        $event->shop->workSpaces()->delete();
        $event->shop->goodLists()->delete();
        $event->shop->sizes()->delete();
        $event->shop->goods()->delete();
        $event->shop->customers()->detach();
    }
}
