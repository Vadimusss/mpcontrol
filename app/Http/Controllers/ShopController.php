<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Shop;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use App\Rules\UniqueCustomer;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // dump($request->user()->ownShops()->get());
        return Inertia::render('Shops/Index', [
            'ownShops' => $request->user()->ownShops()->get(),
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
    public function store(Request $request): RedirectResponse
    {
        if ($request->has(['name', 'key'])) {
            $validated = $request->validate([
                'name' => 'required|unique:shops,name|string|max:255',
                'key' => 'required|unique:api_keys,key|max:500',
            ]);
    
            $shop = $request->user()->ownShops()->create(['name' => $validated['name']]);
    
            $request->user()->ownApiKeys()->create([
                'key' => $validated['key'],
                'shop_id' => $shop->id,
            ]);
        }
        elseif ($request->has(['email', 'shopId'])) {
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'exists:users,email', 'email', 'max:255'],
                'shopId' => ['required', 'integer', 'min:1', 'max:999', new UniqueCustomer],
             ])->stopOnFirstFailure(true);

            $validated = $validator->validate();

            $shop = Shop::find($validated['shopId']);
            $user = User::firstWhere('email', $validated['email']);
            $shop->customers()->attach($user->id);
        }
 
        return redirect(route('shops.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Shop $shop)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shop $shop)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shop $shop)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shop $shop)
    {
        //
    }
}
