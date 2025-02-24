<?php

namespace App\Jobs;

use App\Models\ApiKey;
use App\Services\WbApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Carbon;

class СheckApiKey implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public ApiKey $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $api = new WbApiService($this->apiKey->key);
        $isKeyActive = $api->makeDiscountsPricesApiPing();

        $this->apiKey->is_active = $isKeyActive;

        $payload = json_decode(base64_decode(explode('.', $this->apiKey->key)[1]), true);
        $date = Carbon::createFromTimestamp($payload['exp']);
        $this->apiKey->expires_at = $date->format('Y-m-d H:i:s');

        $this->apiKey->save();
    }
}
