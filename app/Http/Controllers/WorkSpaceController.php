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
        $workSpaces = $shop->workSpaces->map(function ($workSpace) {
            return [
                'id' => $workSpace->id,
                'name' => $workSpace->name,
                'creator' => [
                    'id' => $workSpace->creator->id,
                    'name' => $workSpace->creator->name,
                    'email' => $workSpace->creator->email,
                ],
            ];
        });

        return Inertia::render('WorkSpaces/Index', [
            'shop' => $shop,
            'workSpaces' => $workSpaces,
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


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WorkSpace $workSpace)
    {
        //
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
