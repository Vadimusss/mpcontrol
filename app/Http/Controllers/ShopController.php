<?php

namespace App\Http\Controllers;

use Closure;
use App\Models\User;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use App\Rules\UniqueCustomer;
use App\Rules\ApiKeyIsWorking;
use App\Rules\NotOwner;
use Illuminate\Support\Facades\Gate;
use App\Events\ShopDeleted;
use App\Events\ShopCreated;
use App\Jobs\UpdateNsiFromGoogleSheets;

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
            'settings' => 'required|nullable|array',
            'settings.commission' => 'required|nullable|integer',
            'settings.logistics' => 'required|nullable|integer',
            'settings.percentile_coefficient' => 'required|nullable|numeric',
            'settings.weight_coefficient' => 'required|nullable|numeric',
            'settings.gsheet_url' => 'required|string|max:255|url',
        ]);

        $shop = $request->user()->ownShops()->create([
            'name' => $validated['name'],
            'settings' => $validated['settings'],
        ]);

        $shop->reports()->createMany([
            ['type_id' => 1],
            ['type_id' => 2],
        ]);

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
            case 'update_nsi':
                UpdateNsiFromGoogleSheets::dispatch($shop->id);
                break;
            case 'changeSettings':
                $rules = [
                    'name' => 'required|string|max:255',
                    'settings' => 'nullable|array',
                    'settings.commission' => 'nullable|integer',
                    'settings.logistics' => 'nullable|integer',
                    'settings.percentile_coefficient' => 'nullable|numeric',
                    'settings.weight_coefficient' => 'nullable|numeric',
                    'settings' => ['required', 'array'],
            'settings.gsheet_url' => ['required', 'string', 'max:255'],
                ];

                if ($request['key']) {
                    $rules['key'] = [
                        'required',
                        'unique:api_keys',
                        'max:500',
                        new ApiKeyIsWorking,
                    ];
                }

                $validated = $request->validate($rules);

                $updateData = [
                    'name' => $validated['name'],
                    'settings' => array_merge(
                        $shop->settings ?? [],
                        $validated['settings'] ?? []
                    )
                ];

                $shop->update($updateData);

                if (!empty($validated['key'])) {
                    $shop->apiKey()->update(['key' => $validated['key']]);
                }
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
