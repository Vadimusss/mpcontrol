<?php

namespace App\Http\Controllers;

use App\Services\ViewHandlers\ViewHandlerFactory;
use App\Models\WorkSpace;
use App\Models\Shop;
use App\Models\View;
use App\Models\ViewState;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;
use App\Events\WorkSpaceDeleted;

class WorkSpaceController extends Controller
{
    public function index(Shop $shop): Response
    {
        return Inertia::render('WorkSpaces/Index', [
            'shop' => $shop,
            'workSpaces' => $shop->workSpaces,
            'goodLists' => $shop->goodLists,
            'views' => View::all(),
        ]);
    }

    public function store(Request $request, Shop $shop): RedirectResponse
    {
        Gate::authorize('update', $shop);

        $validated = $request->validate([
            'name' => 'required|unique:work_spaces,name|string|max:255',
            'goodListId' => 'required|integer',
            'view_id' => 'required|integer',
            'settings' => 'required|array',
        ]);

        $workSpace = $shop->workSpaces()->create([
            'name' => $validated['name'],
            'user_id' => $request->user()->id,
            'view_id' => $request['view_id'],
        ]);

        $workSpace->connectedGoodLists()->attach($validated['goodListId']);

        $workSpace->viewSettings()->create([
            'view_id' => $request['view_id'],
            'settings' => json_encode($request['settings']),
        ]);

        return redirect(route('shops.workspaces.index', $shop->id));
    }

    public function show(Request $request, Shop $shop, WorkSpace $workspace)
    {
        $handler = ViewHandlerFactory::make($workspace->viewSettings->view->type);

        $viewState = ViewState::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'workspace_id' => $workspace->id,
                'view_id' => $workspace->viewSettings->view_id,
            ],
            [
                'view_state' => $handler->getDefaultViewState(),
            ]
        );

        return Inertia::render("WorkSpace/{$handler->getComponent()}/index", [
            'shop' => $shop,
            'workSpace' => $workspace,
            'goods' => $handler->prepareData($workspace),
            'initialViewState' => $viewState->view_state ?? [
                'expandedRows' => [],
                'selectedItems' => [],
                'showOnlySelected' => false,
            ],
        ]);
    }

    public function update(Request $request, Shop $shop, WorkSpace $workspace)
    {
        Gate::authorize('update', $workspace);

        $validated = $request->validate([
            'goodListId' => 'required|integer',
            'view_id' => 'required|integer',
            'settings' => 'required|array',
        ]);

        $workspace->connectedGoodLists()->detach();
        $workspace->connectedGoodLists()->attach($validated['goodListId']);

        $workspace->viewSettings()->delete();
        $workspace->viewSettings()->create([
            'view_id' => $request['view_id'],
            'settings' => json_encode($request['settings']),
        ]);
    }

    public function destroy(Shop $shop, WorkSpace $workspace): RedirectResponse
    {
        Gate::authorize('delete', $workspace);

        WorkSpaceDeleted::dispatch($workspace);

        return redirect(route('shops.workspaces.index', $shop->id));
    }
}
