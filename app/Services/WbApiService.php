<?php

namespace App\Services;

use Illuminate\Support\Sleep;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class WbApiService
{
    protected $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getApiV2ListGoods($limit, $offset = 0)
    {

        $response = Http::withToken($this->apiKey)->retry(3, 1000)->get('https://discounts-prices-api.wildberries.ru/api/v2/list/goods/filter', [
            'limit' => $limit,
            'offset' => $offset,
        ]);

        $response->throw();

        return $response->collect(['data', 'listGoods']);
    }

    public function getFullApiV2ListGoods()
    {
        $fullData = collect([]);

        $attempt = 0;
        $offset = 0;
        while ($attempt < 10) {
            $data = $this->getApiV2ListGoods(1000, $offset);

            if ($data->isEmpty()) {
                break;
            }

            if ($data->isNotEmpty()) {
                $fullData = $fullData->concat($data);
                $offset = $offset + 1000;
                continue;
            }

            $attempt++;
        }

        return $fullData;
    }

    public function getApiV2NmReportDetailHistory(array $nmIDs, array $period)
    {
        $response = Http::withToken($this->apiKey)->retry(3, function ($attempt, $e) {
            return str_contains($e->getMessage(), 'timed out') ? 0 : 20000;
        }, throw: false)->post('https://seller-analytics-api.wildberries.ru/api/v2/nm-report/detail/history', [
            'nmIDs' => $nmIDs,
            'period' => $period,
        ]);

        $response->throw();

        return $response->collect(['data']);
    }

    public function getApiV2ListGoodsFilter(int $filterNmID)
    {
        $response = Http::get('https://discounts-prices-api.wildberries.ru/api/v2/list/goods/filter');

        $response->throw();

        return $response->getBody()->getContents();
    }

    public function getAdvV1Upd(array $period)
    {
        $response = Http::withToken($this->apiKey)->retry([1000, 5000, 20000, 60000])->get('https://advert-api.wildberries.ru/adv/v1/upd', [
            'from' => $period['begin'],
            'to' => $period['end'],
        ]);

        $response->throw();

        return $response->collect();
    }

    public function getApiV1SupplierOrders(string $dateFrom, $flag = 1)
    {
        $response = Http::withToken($this->apiKey)->retry(3, 60000)->get('https://statistics-api.wildberries.ru/api/v1/supplier/orders', [
            'dateFrom' => $dateFrom,
            'flag' =>  $flag,
        ]);

        $response->throw();

        Sleep::for(60)->seconds();

        return $response->collect();
    }

    public function getApiV1SupplierStocks(string $dateFrom)
    {
        $response = Http::withToken($this->apiKey)->retry([1000, 5000, 10000, 15000])->get('https://statistics-api.wildberries.ru/api/v1/supplier/stocks', [
            'dateFrom' => $dateFrom,
        ]);

        $response->throw();

        return $response->collect();
    }

    public function makeDiscountsPricesApiPing()
    {
        $response = Http::withToken($this->apiKey)->retry(3, 1000, throw: false)->get('https://discounts-prices-api.wildberries.ru/ping');

        return $response->successful();
    }

    public function makeAdvertApiPing()
    {
        $response = Http::withToken($this->apiKey)->retry(3, 1000, throw: false)->get('https://advert-api.wildberries.ru/ping');

        return $response->successful();
    }

    public function getAdvV1PromotionCount()
    {
        $response = Http::withToken($this->apiKey)
            ->retry(3, 1000)
            ->get('https://advert-api.wildberries.ru/adv/v1/promotion/count');

        $response->throw();

        return $response->collect('adverts')
            ->map(function ($advert) {
                return collect($advert['advert_list'])
                    ->map(function ($item) use ($advert) {
                        return [
                            'type' => $advert['type'],
                            'status' => $advert['status'],
                            'advert_id' => $item['advertId'],
                            'change_time' => $item['changeTime']
                        ];
                    });
            })
            ->flatten(1);
    }

    public function getWbAdvV2Fullstats(array $payload)
    {
        $response = Http::withToken($this->apiKey)
            ->retry(3, 60000, function ($exception, $request) {
                if ($exception instanceof RequestException) {
                    $statusCode = $exception->response->status();
                    return in_array($statusCode, [400]) || $statusCode >= 500;
                }
                return $exception instanceof ConnectionException;
            }, throw: false)
            ->post('https://advert-api.wildberries.ru/adv/v2/fullstats', $payload);

        $response->throw();

        return $response->collect();
    }

    public function createNmReportDownload(array $params): array
    {
        $uuid = Str::uuid()->toString();
        
        $payload = [
            'id' => $uuid,
            'reportType' => 'DETAIL_HISTORY_REPORT',
            'params' => $params
        ];
        
        $response = Http::withToken($this->apiKey)
            ->retry(3, 1000)
            ->post('https://seller-analytics-api.wildberries.ru/api/v2/nm-report/downloads', $payload);
        
        $response->throw();
        
        return [
            'id' => $uuid,
            'response' => $response->collect()
        ];
    }

    public function getNmReportDownloads()
    {
        $response = Http::withToken($this->apiKey)
            ->retry(3, 1000)
            ->get('https://seller-analytics-api.wildberries.ru/api/v2/nm-report/downloads');
        
        $response->throw();
        return $response->collect('data');
    }

    public function retryNmReportDownload(string $downloadId)
    {
        $response = Http::withToken($this->apiKey)
            ->retry(3, 1000)
            ->post('https://seller-analytics-api.wildberries.ru/api/v2/nm-report/downloads/retry', [
                'downloadId' => $downloadId
            ]);
        
        $response->throw();
        return $response->collect();
    }

    public function getNmReportFile(string $downloadId)
    {
        $response = Http::withToken($this->apiKey)
            ->retry(3, 1000)
            ->get("https://seller-analytics-api.wildberries.ru/api/v2/nm-report/downloads/file/{$downloadId}");
        
        $response->throw();
        return $response->body();
    }

    public function getApiV3Warehouses()
    {
        $response = Http::withToken($this->apiKey)
            ->retry(3, 1000)
            ->get('https://marketplace-api.wildberries.ru/api/v3/warehouses');
        
        $response->throw();
        return $response->collect();
    }

    public function getApiV3Stocks(int $warehouseId, array $skus)
    {
        $response = Http::withToken($this->apiKey)
            ->retry(3, 1000)
            ->post("https://marketplace-api.wildberries.ru/api/v3/stocks/{$warehouseId}", [
                'skus' => $skus
            ]);
        
        $response->throw();
        return $response->collect('stocks');
    }
}
