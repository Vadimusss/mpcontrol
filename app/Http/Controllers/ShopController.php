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
use App\Rules\ApiKeyIsWorking;
use App\Rules\NotOwner;
use Illuminate\Support\Facades\Gate;
use App\Events\ShopDeleted;
use App\Events\ShopCreated;

class ShopController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Shops/Index', [
            'ownShops' => $request->user()->ownShops()->with(['apiKey' => function ($query) {
                $query->select('expires_at', 'is_active', 'updated_at', 'shop_id');
            }])->get(),
            'availableShops' => $request->user()->availableShops()->with(['apiKey' => function ($query) {
                $query->select('expires_at', 'is_active', 'updated_at', 'shop_id');
            }])->get(),
        ]);
    }

    public function store(Request $request, Shop $shop): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|unique:shops,name|string|max:255',
            'key' => [
                'required',
                'unique:api_keys,key',
                'max:500',
                new ApiKeyIsWorking,
            ],
        ]);

        $shop = $request->user()->ownShops()->create(['name' => $validated['name']]);

        $request->user()->ownApiKeys()->create([
            'key' => $validated['key'],
            'shop_id' => $shop->id,
        ]);

        ShopCreated::dispatch($shop);
 
        return redirect(route('shops.index'));
    }

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
                    'key' => [
                        'required',
                        'unique:api_keys,key',
                        'max:500',
                        new ApiKeyIsWorking,
                    ],
                ]);

                $shop->apiKey()->update($validated);
                break;
        }

        return redirect(route('shops.index'));
    }

    public function destroy(Shop $shop): RedirectResponse
    {
        Gate::authorize('delete', $shop);

        ShopDeleted::dispatch($shop);
        $shop->delete();

        return redirect(route('shops.index'));
    }
}
