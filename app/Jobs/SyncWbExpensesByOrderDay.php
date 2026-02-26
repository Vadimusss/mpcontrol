<?php

namespace App\Jobs;

use App\Models\Shop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\DB;
use App\Events\JobSucceeded;
use App\Events\JobFailed;
use Throwable;
use Carbon\Carbon;

class SyncWbExpensesByOrderDay implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Shop $shop,
        public Carbon $startDate,
        public Carbon $endDate,
    ) {
        $this->shop = $shop;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public $timeout = 600;
    public $tries = 2;
    public $backoff = 60;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        $externalConnection = DB::connection('ozon_api');

        $query = $externalConnection->table('wb.mv_expenses_by_orderday')
            ->where('cabinet', $this->shop->id)
            ->whereBetween('order_date', [$this->startDate->toDateString(), $this->endDate->toDateString()])
            ->orderBy('order_date')
            ->orderBy('nm_id');

        $totalRecords = $query->count();

        if ($totalRecords === 0) {
            $message = "Нет данных в wb.mv_expenses_by_orderday для магазина {$this->shop->name} за период с {$this->startDate->toDateString()} по {$this->endDate->toDateString()}";

            $duration = microtime(true) - $startTime;
            JobSucceeded::dispatch('SyncWbExpensesByOrderDay', $duration, $message);
            return;
        }

        DB::table('wb_expenses_by_order_days')
            ->where('shop_id', $this->shop->id)
            ->whereBetween('order_date', [$this->startDate->toDateString(), $this->endDate->toDateString()])
            ->delete();

        $insertedCount = 0;
        $query->chunk(5000, function ($chunk) use (&$insertedCount) {
            $dataToInsert = [];

            foreach ($chunk as $row) {
                $dataToInsert[] = [
                    'shop_id' => $row->cabinet,
                    'order_date' => $row->order_date,
                    'nm_id' => $row->nm_id,
                    'orders_count' => $row->orders_count,
                    'op_after_spp' => $row->op_after_spp,
                    'logistics_total' => $row->logistics_total,
                    'amount_to_transfer' => $row->amount_to_transfer,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($dataToInsert)) {
                DB::table('wb_expenses_by_order_days')->insert($dataToInsert);
                $insertedCount += count($dataToInsert);
            }
        });

        $message = "Данные wb.mv_expenses_by_orderday успешно синхронизированы для магазина {$this->shop->name}. Период: с {$this->startDate->toDateString()} по {$this->endDate->toDateString()}. Записей: {$insertedCount}";

        $duration = microtime(true) - $startTime;
        JobSucceeded::dispatch('SyncWbExpensesByOrderDay', $duration, $message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        JobFailed::dispatch('SyncWbExpensesByOrderDay', $exception);
    }
}
