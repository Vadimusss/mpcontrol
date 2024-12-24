<?php

namespace App\Listeners;

use App\Events\WorkSpaceDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteWorkSpaceData
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
    public function handle(WorkSpaceDeleted $event): void
    {
        $event->workspace->connectedGoodLists()->detach();
        $event->workspace->delete();
    }
}
