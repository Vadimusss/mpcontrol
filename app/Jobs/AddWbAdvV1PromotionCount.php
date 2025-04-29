<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Services\WbApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\JobFailed;
use Throwable;

class AddWbAdvV1PromotionCount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, Batchable, SerializesModels;

    public function __construct(
        public Shop $shop
    ) {}

    public function handle()
    {
        $api = new WbApiService($this->shop->apiKey->key);
        $promotions = $api->getAdvV1PromotionCount();

        $this->shop->wbAdvV1PromotionCounts()->delete();

        $this->shop->wbAdvV1PromotionCounts()->createMany(
            $promotions->toArray()
        );
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('AddAdvV1PromotionCount', $exception);
    }
}
