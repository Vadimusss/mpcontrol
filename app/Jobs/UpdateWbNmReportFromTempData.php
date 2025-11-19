<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\Good;
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
            ->orderBy('open_card_count', 'desc')
            ->get()
            ->unique('nm_id');

        if ($tempData->isEmpty()) {
            Log::warning("No temp data found for shop {$this->shop->id} and date {$this->date}");
            return;
        }

        $nmIds = $tempData->pluck('nm_id')->toArray();

        $goods = Good::where('shop_id', $this->shop->id)
            ->whereIn('nm_id', $nmIds)
            ->get()
            ->keyBy('nm_id');

        $goodIds = $goods->pluck('id')->toArray();

        $mainRecords = WbNmReportDetailHistory::whereIn('good_id', $goodIds)
            ->where('dt', $this->date)
            ->get()
            ->keyBy('good_id');

        $updatedCount = 0;
        $skippedCount = 0;

        $updates = [];

        foreach ($tempData as $tempRecord) {
            $good = $goods->get($tempRecord->nm_id);

            if (!$good) {
                $skippedCount++;
                continue;
            }

            $mainRecord = $mainRecords->get($good->id);

            if (!$mainRecord) {
                $skippedCount++;
                continue;
            }

            $updates[] = [
                'id' => $mainRecord->id,
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
            ];
        }

        if (!empty($updates)) {
            $updatedCount = $this->bulkUpdate($updates);
        }

        $duration = microtime(true) - $startTime;

        $message = "Updated {$updatedCount} records for shop {$this->shop->id} for date {$this->date}";
        if ($skippedCount > 0) {
            $message .= " (skipped {$skippedCount} records - no matching goods or main records)";
        }

        JobSucceeded::dispatch('UpdateWbNmReportFromTempData', $duration, $message);
    }

    protected function bulkUpdate(array $updates): int
    {
        $caseStatements = [];
        $bindings = [];
        $ids = [];

        foreach ($updates as $update) {
            $ids[] = $update['id'];
            foreach ($update as $field => $value) {
                if ($field !== 'id') {
                    $caseStatements[$field][] = "WHEN ? THEN ?";
                    $bindings[] = $update['id'];
                    $bindings[] = $value;
                }
            }
        }

        $sets = [];
        foreach ($caseStatements as $field => $cases) {
            $sets[] = "`{$field}` = CASE `id` " . implode(' ', $cases) . " ELSE `{$field}` END";
        }

        $query = "UPDATE " . (new WbNmReportDetailHistory)->getTable() . " SET " . implode(', ', $sets) . " WHERE `id` IN (" . implode(',', array_fill(0, count($ids), '?')) . ")";
        $bindings = array_merge($bindings, $ids);

        return DB::affectingStatement($query, $bindings);
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('UpdateWbNmReportFromTempData', $exception);
    }
}
