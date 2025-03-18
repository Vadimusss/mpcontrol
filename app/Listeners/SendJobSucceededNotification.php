<?php

namespace App\Listeners;

use App\Events\JobSucceeded;
use App\Services\TelegramNotifier;

class SendJobSucceededNotification
{
    public function __construct(
        private TelegramNotifier $notifier
    ) {}

    public function handle(JobSucceeded $event): void
    {
        $this->notifier->notifyJobSucceeded(
            $event->jobName,
            $event->duration,
            $event->message
        );
    }
}
