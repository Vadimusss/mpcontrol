<?php

namespace App\Jobs;

use App\Models\Good;
use App\Models\Shop;
use App\Services\WbApiService;
use Carbon\CarbonPeriod;
use App\Jobs\UpdateStocksAndOrdersReport;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batchable;

class UpdateWbV1SupplierOrdersHistory implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Shop $shop)
    {
        $this->shop = $shop;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startDate = date('Y-m-d',strtotime("-31 days"));
        $endDate = date('Y-m-d', time());

        $period = CarbonPeriod::create($startDate, $endDate)->toArray();

        $formattedDates = array_map(function ($date) {
                return $date->format('Y-m-d');
        }, $period);

        $jobs = collect($formattedDates)->flatMap(function ($date) {
            return [
                new AddWbV1SupplierOrders($this->shop, $date),
                new UpdateStocksAndOrdersReport($this->shop, $date),
            ];
        });

        Bus::chain($addWbV1SupplierOrdersJobs)->dispatch();
    }
}
