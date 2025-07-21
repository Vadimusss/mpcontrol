<?php

namespace App\Jobs;

use App\Models\Shop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Bus\Batchable;

class ClearAddWbAdvV2FullstatsForDate implements ShouldQueue
{
    use Queueable, Batchable;

    public function __construct(
        private Shop $shop,
        private string $date,
    ) {
        $this->shop = $shop;
        $this->date = $date;
    }

    public function handle(): void
    {
        $this->shop->wbAdvV2FullstatsWbAdverts()->where('date', '=', $this->date)->delete();
    }
}
