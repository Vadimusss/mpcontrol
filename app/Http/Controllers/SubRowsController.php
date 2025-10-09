<?php

namespace App\Http\Controllers;

use App\Models\Good;
use App\Models\WorkSpace;
use App\Services\ViewHandlers\MainViewSubRowsHandler;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SubRowsController extends Controller
{
    public function getSubRows($workspaceId, $goodId)
    {
        $workSpace = WorkSpace::findOrFail($workspaceId);
        $good = Good::findOrFail($goodId);
        
        $service = new MainViewSubRowsHandler();
        $viewSettings = json_decode($workSpace->viewSettings->settings);
        $dates = collect(range(0, $viewSettings->days))->map(function ($day) {
            return Carbon::now()->subDays($day)->format('Y-m-d');
        })->all();
        
        $subRowsData = $service->prepareSubRowsData($good, $workSpace->shop, $dates);
        
        return response()->json($subRowsData);
    }
}
