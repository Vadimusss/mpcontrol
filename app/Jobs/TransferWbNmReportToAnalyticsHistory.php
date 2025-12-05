<?php

namespace App\Jobs;

use App\Models\WbNmReportDetailHistory;
use App\Models\WbAnalyticsV3ProductsHistory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Throwable;

class TransferWbNmReportToAnalyticsHistory implements ShouldQueue
{
    use Queueable;

    public $timeout = 1800; // 30 minutes
    public $tries = 3;

    public function handle(): void
    {
        $endDate = Carbon::now()->format('Y-m-d');
        $startDate = Carbon::now()->subDays(34)->format('Y-m-d');
        
        WbNmReportDetailHistory::whereBetween('dt', [$startDate, $endDate])
            ->chunk(500, function ($chunk) {
                $mappedData = [];
                
                foreach ($chunk as $oldRecord) {
                    $mappedData[] = [
                        'good_id' => $oldRecord->good_id,
                        'nm_id' => $oldRecord->nm_id,
                        'title' => $oldRecord->imt_name ?? '',
                        'vendor_code' => $oldRecord->vendor_code ?? '',
                        'brand_name' => '',
                        'subject_id' => 0,
                        'subject_name' => '',
                        'date' => $oldRecord->dt,
                        'open_count' => $oldRecord->open_card_count,
                        'cart_count' => $oldRecord->add_to_cart_count,
                        'order_count' => $oldRecord->orders_count,
                        'order_sum' => $oldRecord->orders_sum_rub,
                        'buyout_count' => $oldRecord->buyouts_count,
                        'buyout_sum' => $oldRecord->buyouts_sum_rub,
                        'buyout_percent' => $oldRecord->buyout_percent,
                        'add_to_cart_conversion' => $oldRecord->add_to_cart_conversion,
                        'cart_to_order_conversion' => $oldRecord->cart_to_order_conversion,
                        'add_to_wishlist_count' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                if (!empty($mappedData)) {
                    DB::table('wb_analytics_v3_products_histories')->insert($mappedData);
                }
            });
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('TransferWbNmReportToAnalyticsHistory job failed', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
