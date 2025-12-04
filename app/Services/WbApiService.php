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
        $response = Http::withToken($this->apiKey)
            ->timeout(120)
            ->connectTimeout(60)
            ->get('https://statistics-api.wildberries.ru/api/v1/supplier/orders', [
                'dateFrom' => $dateFrom,
                'flag' =>  $flag,
            ]);

        $response->throw();

        Sleep::for(60)->seconds();

        return $response->collect();
    }

    public function getApiV1SupplierStocks(string $dateFrom)
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(180)
            ->connectTimeout(60)
            ->get('https://statistics-api.wildberries.ru/api/v1/supplier/stocks', [
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
            ->timeout(90)
            ->connectTimeout(60)
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

    public function getContentV2CardsList(array $cursor): array
    {
        $payload = [
            'settings' => [
                'cursor' => $cursor,
                'filter' => ['withPhoto' => -1]
            ]
        ];

        $response = Http::withToken($this->apiKey)
            ->timeout(90)
            ->connectTimeout(60)
            ->retry(3, 1000)
            ->post('https://content-api.wildberries.ru/content/v2/get/cards/list', $payload);

        $response->throw();

        return $response->json();
    }

    public function getWbAdvV3Fullstats(array $ids, string $beginDate, string $endDate)
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(180)
            ->connectTimeout(60)
            ->get('https://advert-api.wildberries.ru/adv/v3/fullstats', [
                'ids' => implode(',', $ids),
                'beginDate' => $beginDate,
                'endDate' => $endDate
            ]);

        if ($response->status() === 400) {
            $responseBody = $response->json();
            if (isset($responseBody['detail']) && str_contains($responseBody['detail'], 'there are no statistics for this advertising period')) {
                Log::info('No statistics available for advertising period', [
                    'ids' => $ids,
                    'beginDate' => $beginDate,
                    'endDate' => $endDate
                ]);
                return collect();
            }
        }

        if ($response->status() === 500) {
            $responseBody = $response->json();
            if (isset($responseBody['detail']) && str_contains($responseBody['detail'], 'DeadlineExceeded')) {
                Log::info($responseBody['detail'], [
                    'ids' => $ids,
                    'beginDate' => $beginDate,
                    'endDate' => $endDate
                ]);
            }
        }

        $response->throw();

        return $response->collect();
    }

    public function getWbAdvV1PromotionAdverts(array $advertIds)
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(90)
            ->connectTimeout(60)
            ->retry(3, 1000)
            ->post('https://advert-api.wildberries.ru/adv/v1/promotion/adverts?order=id', $advertIds);

        $response->throw();

        return $response->collect();
    }

    public function getWbAdvV0AuctionAdvert(array $advertIds)
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(90)
            ->connectTimeout(60)
            ->retry(3, 1000)
            ->get('https://advert-api.wildberries.ru/adv/v0/auction/adverts', [
                'ids' => implode(',', $advertIds)
            ]);

        $response->throw();

        return $response->collect('adverts');
    }

    public function getApiAnalyticsV3SalesFunnelProductsHistory(array $nmIds, string $date)
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(120)
            ->connectTimeout(60)
            ->post('https://seller-analytics-api.wildberries.ru/api/analytics/v3/sales-funnel/products/history', [
                'selectedPeriod' => [
                    'start' => $date,
                    'end' => $date,
                ],
                'nmIds' => $nmIds,
            ]);

        $response->throw();

        return $response->collect();
    }
}
