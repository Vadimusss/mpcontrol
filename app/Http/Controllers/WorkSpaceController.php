<?php

namespace App\Http\Controllers;

use App\Models\WorkSpace;
use App\Models\Shop;
use App\Models\Good;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;
use App\Events\WorkSpaceDeleted;

class WorkSpaceController extends Controller
{
    public function index(Request $request, Shop $shop): Response
    {
        return Inertia::render('WorkSpaces/Index', [
            'shop' => $shop,
            'workSpaces' => $shop->workSpaces,
            'goodLists' => $shop->goodLists,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Shop $shop): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|unique:work_spaces,name|string|max:255',
        ]);

        $shop->workSpaces()->create(['name' => $validated['name'], 'user_id' => $request->user()->id]);

        return redirect(route('shops.workspaces.index', $request['shopId']));
    }

    public function show(Shop $shop, WorkSpace $workspace)
    {
        $goods = $workspace->connectedGoodLists->load(['goods'])->flatMap(function ($list) {
            return Good::find($list->goods)->load('wbListGoodRow', 'sizes');
        });

        return Inertia::render('WorkSpace/Index', [
            'shop' => $shop,
            'workSpace' => $workspace,
            'goods' => $goods,
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shop $shop, WorkSpace $workspace)
    {
        Gate::authorize('update', $workspace);

        $validated = $request->validate([
            'goodListId' => 'required|integer',
        ]);

        $workspace->connectedGoodLists()->detach();
        $workspace->connectedGoodLists()->attach($validated['goodListId']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop, WorkSpace $workspace): RedirectResponse
    {
        Gate::authorize('delete', $workspace);

        WorkSpaceDeleted::dispatch($workspace);

        return redirect(route('shops.workspaces.index', $shop->id));
    }
}
