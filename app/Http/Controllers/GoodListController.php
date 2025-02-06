<?php

namespace App\Http\Controllers;

use App\Models\GoodList;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class GoodListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Shop $shop): Response
    {
        $ownGoodLists = [];
        $goodLists = [];

        foreach ($shop->goodLists as $goodList) {
            $item = [
                'id' => $goodList->id,
                'name' => $goodList->name,
                'creator' => [
                    'id' => $goodList->creator->id,
                    'name' => $goodList->creator->name,
                    'email' => $goodList->creator->email,
                ],
            ];
            if ($goodList->creator->id === $request->user()->id) {
                $ownGoodLists[] = $item;
            } else {
                $goodLists[] = $item;
            }
        };

        return Inertia::render('GoodLists/Index', [
            'shop' => $shop,
            'ownGoodLists' => $ownGoodLists,
            'goodLists' => $goodLists,
            'goods' => $shop->goods,
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
    public function store(Request $request, Shop $shop): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|unique:good_lists,name|string|max:255',
            'goodsId' => 'array|min:1',
        ]);

        $goodList = $shop->goodLists()->create(['name' => $validated['name'], 'user_id' => $request->user()->id]);
        $goodList->goods()->attach($validated['goodsId']);

        return redirect(route('shops.goodlists.index', $request['shopId']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Shop $shop, GoodList $goodlist)
    {
        return Inertia::render('GoodList/Index', [
            'shop' => $shop,
            'goodList' => $goodlist,
            'goods' => $goodlist->goods,
            'creator' => $goodlist->creator,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GoodList $goodList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shop $shop, GoodList $goodlist)
    {
        $validated = $request->validate([
            'selectedGoodsId' => 'array|min:1',
        ]);

        switch ($request['type']) {
            case 'add':
                $goodlist->goods()->detach($validated['selectedGoodsId']);
                $goodlist->goods()->attach($validated['selectedGoodsId']);
                break;
            case 'delete':
                $goodlist->goods()->detach($validated['selectedGoodsId']);
                break;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop, GoodList $goodlist)
    {
        Gate::authorize('delete', $goodlist);

        $goodlist->goods()->detach();
        $goodlist->delete();

        return redirect(route('shops.goodlists.index', $shop->id));
    }
}
