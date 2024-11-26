<?php

namespace App\Http\Controllers;

use App\Models\ProductList;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Shop $shop): Response
    {
        return Inertia::render('ProductLists/Index', [
            'shop' => $shop,
            'productLists' => $shop->productLists,
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
            'name' => 'required|unique:product_lists,name|string|max:255',
        ]);

        $shop->productLists()->create(['name' => $validated['name'], 'user_id' => $request->user()->id]);

        return redirect(route('shops.productlists.index', $request['shopId']));
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductList $productList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductList $productList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductList $productList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductList $productList)
    {
        //
    }
}
