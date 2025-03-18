<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramNotifier
{
    protected string $token;
    protected string $chatId;

    public function __construct()
    {
        $this->token = config('services.telegram.token');
        $this->chatId = config('services.telegram.chat_id');
    }

    public function notifyJobSucceeded(string $jobName, float $duration, ?string $message = null): bool
    {
        $text = "✅ Задание *{$jobName}* успешно выполнено за *".round($duration, 2)." сек*";
        
        if ($message) {
            $text .= "\n\n{$message}";
        }

        return $this->sendMessage($text);
    }

    public function sendMessage(string $message): bool
    {
        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";

        $response = Http::post($url, [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => 'true',
        ]);

        return $response->successful();
    }
}
