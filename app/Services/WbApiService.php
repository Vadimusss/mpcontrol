<?php

namespace App\Services;
 
use Illuminate\Support\Facades\Http;
 
class WbApiService
{
    protected $apiKey;
 
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }
 
    public function getApiV2ListGoods($limit, $offset = 0)
    {

        $response = Http::withToken($this->apiKey)->
            retry(3, 1000)->
            get('https://discounts-prices-api.wildberries.ru/api/v2/list/goods/filter', [
                'limit' => $limit,
                'offset' => $offset,
            ]);
 
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

    public function getApiV2ListGoodsFilter(int $filterNmID)
    {
        $response = Http::get('https://discounts-prices-api.wildberries.ru/api/v2/list/goods/filter');
 
        return $response->getBody()->getContents();
    }

    public function test()
    {
        return 'Success!';
    }
}