<?php

namespace App\Listeners;

use App\Events\JobFailed;
use App\Services\TelegramNotifier;

class SendJobFailedNotification
{
    public function __construct(
        private TelegramNotifier $notifier
    ) {}

    public function handle(JobFailed $event): void
    {
        $message = "❌ Job failed: {$event->jobName}\n"
            . "Error: {$event->exception->getMessage()}";

        $this->notifier->sendMessage($message);
    }
}
