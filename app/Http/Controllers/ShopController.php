<?php

namespace App\Http\Controllers;

use Closure;
use App\Models\User;
use App\Models\Shop;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;
use App\Rules\UniqueCustomer;
use App\Rules\NotOwner;
use Illuminate\Support\Facades\Gate;
use App\Events\ShopDeleted;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $availableShops = $request->user()->availableShops->map(function ($shop) {
            return [
                'id' => $shop->id,
                'name' => $shop->name,
                'owner' => [
                    'id' => $shop->owner->id,
                    'name' => $shop->owner->name,
                    'email' => $shop->owner->email,
                ],
            ];
        });

        $ownShops = $request->user()->ownShops->map(function ($shop) {
            $customers =  $shop->customers->map(function ($customer) {
                return ['id' => $customer->id, 'name' => $customer->name, 'email' => $customer->email];
            });

            return [
                'id' => $shop->id,
                'name' => $shop->name,
                'owner' => [
                    'id' => $shop->owner->id,
                    'name' => $shop->owner->name,
                    'email' => $shop->owner->email,
                ],
                'customers' => (count($customers) === 0) ? false : $customers,
            ];
        });

        return Inertia::render('Shops/Index', [
            'ownShops' => $ownShops,
            'availableShops' => $availableShops,
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
    public function update(Request $request, Shop $shop): RedirectResponse
    {
        Gate::authorize('update', $shop);

        switch ($request['type']) {
            case 'addCustomer':
                $validated = $request->validate([
                    'email' => [
                        'required',
                        'exists:users,email',
                        'email',
                        new UniqueCustomer,
                        new NotOwner,
                    ],
                ]);
    
                $user = User::firstWhere('email', $validated['email']);
                $shop->customers()->attach($user->id);
                break;
            case 'deleteCustomer':
                $validated = $request->validate([
                    'customerId' => [
                        'required',
                        'integer',
                    ],
                ]);
    
                $shop->customers()->detach($validated['customerId']);
                break;
            case 'changeApiKey':
                $validated = $request->validate([
                    'key' => 'required', 'unique:api_keys,key', 'max:500',
                ]);

                $shop->apiKey()->update($validated);
                break;
        }

        return redirect(route('shops.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Shop $shop): RedirectResponse
    {
        Gate::authorize('delete', $shop);

        ShopDeleted::dispatch($shop);
        $shop->delete();

        return redirect(route('shops.index'));
    }
}
