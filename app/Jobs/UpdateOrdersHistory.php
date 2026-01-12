<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Jobs\AddWbV1SupplierOrders;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Carbon\Carbon;
use App\Events\JobFailed;
use Throwable;

class UpdateOrdersHistory implements ShouldQueue
{
    use Queueable;

    public function __construct() {}

    public function handle(): void
    {
        $shops = Shop::without(['owner', 'customers'])->whereHas('apiKey', function ($query) {
            $query->where('is_active', true);
        })->get();

        $dates = collect(range(2, 32))->map(function ($day) {
            return Carbon::now()->subDays($day)->format('Y-m-d');
        })->toArray();

        $shops->each(function ($shop) use ($dates) {
            $updateJobs = [];

            foreach ($dates as $date) {
                $updateJobs[] = new AddWbV1SupplierOrders($shop, $date);
                $updateJobs[] = new UpdateStocksAndOrdersReport($shop, $date);
            }

            Bus::chain($updateJobs)->dispatch();
        });
    }

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('UpdateOrdersHistory', $exception);
    }
}
