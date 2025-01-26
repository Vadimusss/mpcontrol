<?php

namespace App\Jobs;

use App\Models\Good;
use App\Services\WbApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;

class AddWbAdvV1Upd implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public string $apiKey,
        public array $period,
        public $timeout = 600,
    )
    {
        $this->apiKey = $apiKey;
        $this->period = $period;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $api = new WbApiService($this->apiKey);
        $WbAdvV1UpdData = $api->getAdvV1Upd($this->period);

        $WbAdvV1UpdData->each(function ($row) {
            $vendorCode = explode(' ', $row['campName'])[0];
            $good = Good::firstWhere('vendor_code', $vendorCode);
            if ($good !== null) {
                $good->WbAdvV1Upd()->create([
                    'upd_num' => $row['updNum'],
                    'upd_time' => $row['updTime'],
                    'upd_sum' => $row['updSum'],
                    'advert_id' => $row['advertId'],
                    'camp_name' => $row['campName'],
                    'advert_type' => $row['advertType'],
                    'payment_type' => $row['paymentType'],
                    'advert_status' => $row['advertStatus'],
                ]);
            }
        });
    }
}
