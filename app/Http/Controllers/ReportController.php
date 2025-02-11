<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\GoodList;
use App\Models\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Shop $shop): Response
    {
        return Inertia::render('Reports/Index', [
            'shop' => $shop,
            'goodLists' => $shop->goodLists,
            'reports' => $shop->reports,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'shopId' =>  'required|integer',
            'reportId' => 'required|integer',
            'goodListId' => 'required|integer',
            'beginDate' => 'required|string',
            'endDate' => 'required|string',
        ]);

        $shop = Shop::find($validated['shopId']);
        $report = Report::find($validated['reportId']);
        $goodList = GoodList::find($validated['goodListId']);

        $begin = $validated['beginDate'];
        $end = $validated['endDate'];

        $report->connectedGoodLists()->detach();
        $report->connectedGoodLists()->attach($goodList->id);

        return Excel::download(new ReportExport($shop, $report, $goodList, $begin, $end), "test.xlsx");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        //
    }
}
