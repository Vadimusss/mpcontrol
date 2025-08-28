<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\TempWbNmReportDetailHistory;
use App\Models\WbNmReportDetailHistory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\JobFailed;
use App\Events\JobSucceeded;
use Throwable;

class UpdateWbNmReportFromTempData implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Shop $shop,
        public string $date
    ) {
        $this->shop = $shop;
        $this->date = $date;
    }

    public function handle(): void
    {
        $startTime = microtime(true);

        $tempData = TempWbNmReportDetailHistory::where('shop_id', $this->shop->id)
            ->where('dt', $this->date)
            ->get();

        if ($tempData->isEmpty()) {
            Log::warning("No temp data found for shop {$this->shop->id} and date {$this->date}");
            return;
        }

        $updatedCount = 0;

        foreach ($tempData as $tempRecord) {
            $updated = $this->updateMainRecord($tempRecord);
            if ($updated) {
                $updatedCount++;
            }
        }

        $duration = microtime(true) - $startTime;

        JobSucceeded::dispatch('UpdateWbNmReportFromTempData', $duration, "Updated {$updatedCount} records for shop {$this->shop->id} for date {$this->date}");
    }

    protected function updateMainRecord(TempWbNmReportDetailHistory $tempRecord): bool
    {
        $mainRecord = WbNmReportDetailHistory::where('nm_id', $tempRecord->nm_id)
            ->where('dt', $tempRecord->dt)
            ->first();

        if (!$mainRecord) {
            return false;
        }

        $updated = $mainRecord->update([
            'open_card_count' => $tempRecord->open_card_count,
            'add_to_cart_count' => $tempRecord->add_to_cart_count,
            'orders_count' => $tempRecord->orders_count,
            'orders_sum_rub' => $tempRecord->orders_sum_rub,
            'buyouts_count' => $tempRecord->buyouts_count,
            'buyouts_sum_rub' => $tempRecord->buyouts_sum_rub,
            'cancel_count' => $tempRecord->cancel_count,
            'cancel_sum_rub' => $tempRecord->cancel_sum_rub,
            'buyout_percent' => $tempRecord->buyout_percent,
            'add_to_cart_conversion' => $tempRecord->add_to_cart_conversion,
            'cart_to_order_conversion' => $tempRecord->cart_to_order_conversion,
        ]);

        return $updated;
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('UpdateWbNmReportFromTempData', $exception);
    }
}
