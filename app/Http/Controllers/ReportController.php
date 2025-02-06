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

    public function export(Request $request, /* Shop $shop,  Report $report */)
    {
/*         $validated = $request->validate([
            'reportId' => 'required|integer',
            'goodListId' => 'required|integer',
            // 'begin' => 'required|string',
            // 'end' => 'required|string',
        ]);

        $report = Report::find($validated['reportId']);
        $goodList = GoodList::find($validated['goodListId']); */
        // $begin = $validated['begin'];
        // $end = $validated['end'];

        // dump($goodList);

        $begin = '2025-02-05';
        $end = '2025-02-05';

        // $report->connectedGoodLists()->detach();
        // $report->connectedGoodLists()->attach($goodList);

        return Excel::download(new ReportExport(/* $report,  */$begin, $end), 'report.xlsx');
        // return Excel::download(new ReportExport(/* $report, */ $begin, $end), "{$reportName} {$begin}-{$end}.xlsx");
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
