<?php

namespace App\Http\Controllers;

use App\Models\Good;
use App\Models\Shop;
use App\Services\ViewHandlers\GoodDetailsModalHandler;
use Illuminate\Http\Request;

class GoodDetailsController extends Controller
{
    public function getGoodDetails(Request $request, Shop $shop, Good $good)
    {
        $request->validate([
            'dates' => 'required|array',
            'dates.*' => 'date',
        ]);

        $dates = $request->input('dates');

        $handler = new GoodDetailsModalHandler();
        $data = $handler->prepareGoodDetailsData($good, $shop, $dates);

        return response()->json($data);
    }
}
