<?php

namespace App\Services\ViewHandlers;

use App\Models\WorkSpace;

class SizesViewHandler implements ViewHandler
{
    public function prepareData(WorkSpace $workSpace): array
    {
        $goods = $workSpace->connectedGoodLists
            ->flatMap(function ($list) {
                return $list->goods;
            })
            ->map(function ($good) {
                $sizes = $good->sizes
                    ->map(function ($size) {
                        return [
                            'size' => $size->size,
                            'quantity' => $size->quantity
                        ];
                    })
                    ->toArray();

                return [
                    'id' => $good->id,
                    'name' => $good->name,
                    'sizes' => $sizes
                ];
            })
            ->toArray();

        return ['goods' => $goods];
    }

    public function getDefaultViewState(): array
    {
        return [];
    }

    public function getComponent(): string
    {
        return 'SizesView';
    }
}
