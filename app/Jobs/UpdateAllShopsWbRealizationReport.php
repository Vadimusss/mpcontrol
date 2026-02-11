<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Jobs\UpdateWbRealizationReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Events\JobFailed;
use Throwable;
use Carbon\Carbon;

class UpdateAllShopsWbRealizationReport implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ?string $date = null,
    ) {
        $this->date = $date ?? Carbon::yesterday()->format('Y-m-d');
    }

    public function handle(): void
    {
        $shops = Shop::without(['owner', 'customers'])->with('goods')->get();

        $shops->each(function ($shop) {
            UpdateWbRealizationReport::dispatch($shop, $this->date);
        });
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('DailyWbApiDataUpdate', $exception);
    }
}
