<?php

namespace App\Jobs;

use Carbon\CarbonPeriod;
use App\Models\Shop;
use App\Jobs\FixSalesFunelReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;

class ReloadAdvertisingCostsData implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public $timeout = 2400,
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $shops = Shop::without(['owner', 'customers'])->get();

        $shops->each(function ($shop, int $key) {
            $startDate = date('Y-m-d', strtotime("-12 days"));
            $endDate = date('Y-m-d', time());
    
            $period = CarbonPeriod::create($startDate, $endDate)->toArray();
    
            $formattedDates = array_map(function ($date) {
                    return $date->format('Y-m-d');
            }, $period);
    
            $jobs = collect($formattedDates)->map(function ($date) use ($shop) {
                return new FixSalesFunelReport($shop, $date);
            });
    
            Bus::chain($jobs)->dispatch();
        });
    }
}
