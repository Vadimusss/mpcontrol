<?php

namespace App\Jobs;

use App\Models\ApiKey;
use App\Services\WbApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use App\Events\СheckApiKeyCompleted;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Carbon;
use App\Events\JobFailed;
use Throwable;

class СheckApiKey implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(public ApiKey $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function handle(): void
    {
        $api = new WbApiService($this->apiKey->key);
        $isKeyActive = $api->makeDiscountsPricesApiPing();

        $this->apiKey->is_active = $isKeyActive;

        $payload = json_decode(base64_decode(explode('.', $this->apiKey->key)[1]), true);
        $date = Carbon::createFromTimestamp($payload['exp']);
        $this->apiKey->expires_at = $date->format('Y-m-d H:i:s');

        $this->apiKey->save();

        СheckApiKeyCompleted::dispatch($this->apiKey->shop_id, $this->apiKey->updated_at);
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('СheckApiKey', $exception);
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->apiKey->id))->releaseAfter(600)->dontRelease()];
    }
}
