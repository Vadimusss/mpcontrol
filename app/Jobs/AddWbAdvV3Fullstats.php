<?php

namespace App\Jobs;

use App\Models\Good;
use App\Models\Shop;
use App\Models\WbAdvV3FullstatsProduct;
use App\Models\WbAdvV3FullstatsBooster;
use App\Services\WbApiService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\JobFailed;
use Illuminate\Support\Collection;
use Throwable;

class AddWbAdvV3Fullstats implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        private Shop $shop,
        private array $ids,
        private string $date,
    ) {
        $this->shop = $shop;
        $this->ids = $ids;
        $this->date = $date;
    }

    public $timeout = 120;
    public $backoff = 1;
    public $tries = 5;

    public function handle(): void
    {
        $api = new WbApiService($this->shop->apiKey->key);

        $response = collect($api->getWbAdvV3Fullstats($this->ids, $this->date, $this->date));
 
        if ($response->isEmpty()) {
            Log::error('Empty API response');
            return;
        }

        $advertIds = $response->pluck('advertId')->unique()->filter()->values()->all();

        if (!empty($advertIds)) {
            DB::table('wb_adv_v3_fullstats_wb_adverts')
                ->where('shop_id', $this->shop->id)
                ->where('date', $this->date)
                ->whereIn('advert_id', $advertIds)
                ->delete();
        }

        $response->each(function ($advertData) {
            try {
                $wbAdvert = $this->shop->wbAdvV3FullstatsWbAdverts()->create([
                    'views' => $advertData['views'] ?? 0,
                    'clicks' => $advertData['clicks'] ?? 0,
                    'ctr' => $advertData['ctr'] ?? 0,
                    'cpc' => $advertData['cpc'] ?? 0,
                    'sum' => $advertData['sum'] ?? 0,
                    'atbs' => $advertData['atbs'] ?? 0,
                    'orders' => $advertData['orders'] ?? 0,
                    'cr' => $advertData['cr'] ?? 0,
                    'shks' => $advertData['shks'] ?? 0,
                    'sum_price' => $advertData['sum_price'] ?? 0,
                    'canceled' => $advertData['canceled'] ?? 0,
                    'date' => $this->date,
                    'advert_id' => $advertData['advertId'] ?? 0
                ]);

                collect($advertData['days'] ?? [])->each(function ($dayData) use ($wbAdvert) {
                    $wbDay = $wbAdvert->wbAdvV3FullstatsDays()->create([
                        'date' => $dayData['date'] ?? '0000-00-00',
                        'views' => $dayData['views'] ?? 0,
                        'clicks' => $dayData['clicks'] ?? 0,
                        'ctr' => $dayData['ctr'] ?? 0,
                        'cpc' => $dayData['cpc'] ?? 0,
                        'sum' => $dayData['sum'] ?? 0,
                        'atbs' => $dayData['atbs'] ?? 0,
                        'orders' => $dayData['orders'] ?? 0,
                        'cr' => $dayData['cr'] ?? 0,
                        'shks' => $dayData['shks'] ?? 0,
                        'sum_price' => $dayData['sum_price'] ?? 0,
                        'canceled' => $dayData['canceled'] ?? 0
                    ]);

                    collect($dayData['apps'] ?? [])->each(function ($appData) use ($wbDay) {
                        $wbApp = $wbDay->wbAdvV3FullstatsApps()->create([
                            'views' => $appData['views'] ?? 0,
                            'clicks' => $appData['clicks'] ?? 0,
                            'ctr' => $appData['ctr'] ?? 0,
                            'cpc' => $appData['cpc'] ?? 0,
                            'sum' => $appData['sum'] ?? 0,
                            'atbs' => $appData['atbs'] ?? 0,
                            'orders' => $appData['orders'] ?? 0,
                            'cr' => $appData['cr'] ?? 0,
                            'shks' => $appData['shks'] ?? 0,
                            'sum_price' => $appData['sum_price'] ?? 0,
                            'canceled' => $appData['canceled'] ?? 0,
                            'app_type' => $appData['appType'] ?? 0
                        ]);

                        collect($appData['nms'] ?? [])->each(function ($productData) use ($wbApp) {
                            $good = Good::where('nm_id', $productData['nmId'])->first();
                            if ($good) {
                                WbAdvV3FullstatsProduct::create([
                                    'wb_adv_v3_fs_app_id' => $wbApp->id,
                                    'good_id' => $good->id,
                                    'date' => $this->date,
                                    'views' => $productData['views'] ?? 0,
                                    'clicks' => $productData['clicks'] ?? 0,
                                    'ctr' => $productData['ctr'] ?? 0,
                                    'cpc' => $productData['cpc'] ?? 0,
                                    'sum' => $productData['sum'] ?? 0,
                                    'atbs' => $productData['atbs'] ?? 0,
                                    'orders' => $productData['orders'] ?? 0,
                                    'cr' => $productData['cr'] ?? 0,
                                    'shks' => $productData['shks'] ?? 0,
                                    'sum_price' => $productData['sum_price'] ?? 0,
                                    'canceled' => $productData['canceled'] ?? 0,
                                    'name' => $productData['name'] ?? '',
                                    'nm_id' => $productData['nmId'] ?? 0
                                ]);
                            }
                        });
                    });
                });

                collect($advertData['boosterStats'] ?? [])->each(function ($boosterData) use ($wbAdvert) {
                    WbAdvV3FullstatsBooster::create([
                        'wb_adv_v3_fullstats_wb_advert_id' => $wbAdvert->id,
                        'avg_position' => $boosterData['avg_position'] ?? 0,
                        'date' => $boosterData['date'] ?? '0000-00-00',
                        'nm_id' => $boosterData['nm'] ?? 0
                    ]);
                });
            } catch (Throwable $e) {
                Log::error('Error processing advert data', ['error' => $e->getMessage()]);
            }
        });
    }

    public function failed(?Throwable $exception): void
    {
        $this->dispatchNextJobInChain();
        JobFailed::dispatch('AddWbAdvV3Fullstats', $exception);
        Log::error($exception->getMessage());
    }
}
