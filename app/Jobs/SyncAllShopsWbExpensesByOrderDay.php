<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Jobs\SyncWbExpensesByOrderDay;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use App\Events\JobFailed;
use Throwable;
use Carbon\Carbon;

class SyncAllShopsWbExpensesByOrderDay implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ?string $startDate = null,
        public ?string $endDate = null,
        public int $daysBack = 32
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->daysBack = $daysBack;
    }

    public function handle(): void
    {
        $shops = Shop::without(['owner', 'customers'])->with('goods')->get();

        $endDate = $this->endDate
            ? Carbon::parse($this->endDate)
            : Carbon::yesterday();

        $startDate = $this->startDate
            ? Carbon::parse($this->startDate)
            : $endDate->copy()->subDays($this->daysBack);

        $shops->each(function ($shop) use ($startDate, $endDate) {
            SyncWbExpensesByOrderDay::dispatch($shop, $startDate, $endDate);
        });
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('DailyWbApiDataUpdate', $exception);
    }
}
