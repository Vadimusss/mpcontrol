<?php

namespace App\Http\Controllers;

use App\Models\WorkSpace;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class WorkSpaceController extends Controller
{
    public function index(Request $request, Shop $shop): Response
    {
        $ownWorkSpaces = [];
        $workSpaces = [];

        foreach ($shop->workSpaces as $workSpace) {
            $item = [
                'id' => $workSpace->id,
                'name' => $workSpace->name,
                'creator' => [
                    'id' => $workSpace->creator->id,
                    'name' => $workSpace->creator->name,
                    'email' => $workSpace->creator->email,
                ],
            ];
            if ($workSpace->creator->id === $request->user()->id) {
                $ownWorkSpaces[] = $item;
            } else {
                $workSpaces[] = $item;
            }
        };

        return Inertia::render('WorkSpaces/Index', [
            'shop' => $shop,
            'ownWorkSpaces' => $ownWorkSpaces,
            'workSpaces' => $workSpaces,
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
        return Inertia::render('WorkSpace/Index', [
            'shop' => $shop,
            'workSpace' => $workspace,
            'goodlists' => $shop->goodLists,
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shop $shop, WorkSpace $workspace)
    {
        dump($workspace);
        Gate::authorize('update', $workspace);

        $validated = $request->validate([
            'goodListId' => 'required|integer',
        ]);

        dump($validated['goodListId']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop, WorkSpace $workspace): RedirectResponse
    {
        Gate::authorize('delete', $workspace);

        $workspace->delete();

        return redirect(route('shops.workspaces.index', $shop->id));
    }
}
