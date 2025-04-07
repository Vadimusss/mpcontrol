<?php

namespace App\Jobs;

use App\Models\Good;
use App\Models\Shop;
use App\Services\WbApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use App\Events\JobFailed;
use Throwable;

class AddWbAdvV1Upd implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public Shop $shop,
        public array $period,
        public $timeout = 600,
    ) {
        $this->shop = $shop;
        $this->period = $period;
    }

    public function handle(): void
    {
        $day = $this->period['begin'];
        $this->shop->WbAdvV1Upd()->where('upd_time', 'like', "%{$day}%")->delete();

        $api = new WbApiService($this->shop->apiKey->key);
        $WbAdvV1UpdData = $api->getAdvV1Upd($this->period);

        $WbAdvV1UpdData->each(function ($row) {
            $vendorCode = explode(' ', $row['campName'])[0];
            $good = Good::firstWhere([
                'vendor_code' => $vendorCode,
                'shop_id' => $this->shop->id,
            ]);
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

    public function failed(?Throwable $exception): void
    {
        JobFailed::dispatch('AddWbAdvV1Upd', $exception);
    }
}
