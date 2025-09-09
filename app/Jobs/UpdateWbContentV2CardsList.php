<?php

namespace App\Jobs;

use App\Models\Shop;
use App\Models\WbContentV2CardsList;
use App\Models\WbContentV2CardsListPhoto;
use App\Models\WbContentV2CardsListCharacteristic;
use App\Models\WbContentV2CardsListSize;
use App\Models\WbContentV2CardsListTag;
use App\Models\WbContentV2CardsListDimension;
use App\Services\WbApiService;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Exception;

class UpdateWbContentV2CardsList implements ShouldQueue
{
    use Queueable;

    protected $shop;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    public function handle(): void
    {
        try {
            $apiService = new WbApiService($this->shop->apiKey->key);
            $limit = 100;
            $cursor = ['limit' => $limit];
            $hasMore = true;

            while ($hasMore) {
                $response = $apiService->getContentV2CardsList($cursor);
                $cards = $response['cards'] ?? [];

                $total = $response['cursor']['total'];

                if ($total >= $limit) {
                    $cursor['nmID'] = $response['cursor']['nmID'];
                    $cursor['updatedAt'] = $response['cursor']['updatedAt'];
                    $cursor['limit'] = $limit;
                    $hasMore = true;
                } else {
                    $hasMore = false;
                }
                $this->processCardsBatch($cards);
                if ($hasMore) {
                    sleep(1);
                }
            }
        } catch (Exception $e) {
            Log::error("Error in TestWbContentV2CardsList for shop {$this->shop->id}: " . $e->getMessage());
            throw $e;
        }
    }

    protected function processCardsBatch(array $chunk): void
    {
        try {
            $nmIds = array_column($chunk, 'nmID');

            WbContentV2CardsList::where('shop_id', $this->shop->id)
                ->whereIn('nm_id', $nmIds)
                ->delete();

            $insertData = [];

            foreach ($chunk as $cardData) {
                $nmId = $cardData['nmID'];

                $createdAtApi = $cardData['createdAt'] ?? null;
                $updatedAtApi = $cardData['updatedAt'] ?? null;

                if ($createdAtApi) {
                    $createdAtApi = Carbon::parse($createdAtApi)->format('Y-m-d H:i:s');
                }

                if ($updatedAtApi) {
                    $updatedAtApi = Carbon::parse($updatedAtApi)->format('Y-m-d H:i:s');
                }

                $insertData[] = [
                    'shop_id' => $this->shop->id,
                    'nm_id' => $nmId,
                    'imt_id' => $cardData['imtID'] ?? null,
                    'nm_uuid' => $cardData['nmUUID'] ?? null,
                    'subject_id' => $cardData['subjectID'] ?? null,
                    'subject_name' => $cardData['subjectName'] ?? null,
                    'vendor_code' => $cardData['vendorCode'] ?? null,
                    'brand' => $cardData['brand'] ?? null,
                    'title' => $cardData['title'] ?? null,
                    'description' => $cardData['description'] ?? null,
                    'video' => $cardData['video'] ?? null,
                    'need_kiz' => $cardData['needKiz'] ?? false,
                    'wholesale_enabled' => $cardData['wholesaleEnabled'] ?? false,
                    'wholesale_quantum' => $cardData['wholesaleQuantum'] ?? null,
                    'created_at_api' => $createdAtApi,
                    'updated_at_api' => $updatedAtApi,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($insertData)) {
                WbContentV2CardsList::insert($insertData);

                $insertedCards = WbContentV2CardsList::where('shop_id', $this->shop->id)
                    ->whereIn('nm_id', $nmIds)
                    ->get()
                    ->keyBy('nm_id');

                $photosData = [];
                $characteristicsData = [];
                $sizesData = [];
                $tagsData = [];
                $dimensionsData = [];

                foreach ($chunk as $cardData) {
                    $nmId = $cardData['nmID'];
                    $card = $insertedCards[$nmId] ?? null;

                    if ($card) {
                        if (isset($cardData['photos'])) {
                            foreach ($cardData['photos'] as $photoData) {
                                $photosData[] = [
                                    'wb_cards_list_id' => $card->id,
                                    'big' => $photoData['big'] ?? null,
                                    'c246x328' => $photoData['c246x328'] ?? null,
                                    'c516x688' => $photoData['c516x688'] ?? null,
                                    'square' => $photoData['square'] ?? null,
                                    'tm' => $photoData['tm'] ?? null,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }

                        foreach ($cardData['characteristics'] ?? [] as $charData) {
                            $values = $charData['value'] ?? [];
                            $valuesText = is_array($values) ? implode(', ', $values) : (string)$values;

                            $characteristicsData[] = [
                                'wb_cards_list_id' => $card->id,
                                'characteristic_id' => $charData['id'] ?? null,
                                'name' => $charData['name'] ?? '',
                                'values_text' => $valuesText,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }

                        foreach ($cardData['sizes'] ?? [] as $sizeData) {
                            $skus = $sizeData['skus'] ?? [];
                            $skusText = implode(', ', $skus);

                            $sizesData[] = [
                                'wb_cards_list_id' => $card->id,
                                'chrt_id' => $sizeData['chrtID'] ?? null,
                                'tech_size' => $sizeData['techSize'] ?? null,
                                'wb_size' => $sizeData['wbSize'] ?? null,
                                'price' => $sizeData['price'] ?? null,
                                'skus_text' => $skusText,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }

                        foreach ($cardData['tags'] ?? [] as $tagData) {
                            $tagsData[] = [
                                'wb_cards_list_id' => $card->id,
                                'tag_id' => $tagData['id'] ?? null,
                                'name' => $tagData['name'] ?? '',
                                'color' => $tagData['color'] ?? null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }

                        if (!empty($cardData['dimensions'])) {
                            $dimensionsData[] = [
                                'wb_cards_list_id' => $card->id,
                                'width' => $cardData['dimensions']['width'] ?? null,
                                'height' => $cardData['dimensions']['height'] ?? null,
                                'length' => $cardData['dimensions']['length'] ?? null,
                                'weight_brutto' => $cardData['dimensions']['weightBrutto'] ?? null,
                                'is_valid' => $cardData['dimensions']['isValid'] ?? false,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }

                $this->insertRelatedData($photosData, $characteristicsData, $sizesData, $tagsData, $dimensionsData);
            }
        } catch (Exception $e) {
            Log::error("Error processing chunk for shop {$this->shop->id}: " . $e->getMessage());
            throw $e;
        }
    }

    protected function insertRelatedData(
        array $photosData,
        array $characteristicsData,
        array $sizesData,
        array $tagsData,
        array $dimensionsData
    ): void {
        try {
            if (!empty($photosData)) {
                WbContentV2CardsListPhoto::insert($photosData);
            }

            if (!empty($characteristicsData)) {
                WbContentV2CardsListCharacteristic::insert($characteristicsData);
            }

            if (!empty($sizesData)) {
                WbContentV2CardsListSize::insert($sizesData);
            }

            if (!empty($tagsData)) {
                WbContentV2CardsListTag::insert($tagsData);
            }

            if (!empty($dimensionsData)) {
                WbContentV2CardsListDimension::insert($dimensionsData);
            }
        } catch (Exception $e) {
            Log::error("Error inserting related data: " . $e->getMessage());
        }
    }
}
