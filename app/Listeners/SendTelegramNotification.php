<?php

namespace App\Listeners;

use App\Events\JobFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;

class SendTelegramNotification implements ShouldQueue
{
    public function handle(JobFailed $event)
    {
        $message = "⚠️ Job Failed: {$event->jobName}\n"
            . "Error: {$event->exception->getMessage()}";

        Http::post('https://api.telegram.org/bot' . config('services.telegram.token') . '/sendMessage', [
            'chat_id' => config('services.telegram.chat_id'),
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }
}
