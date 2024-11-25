<?php

namespace App\Http\Controllers;

use App\Models\WorkSpace;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class WorkSpaceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|unique:shops,name|string|max:255',
        ]);

        $shop = Shop::find($request['shopId']);
        $shop->workSpaces()->create(['name' => $validated['name'], 'user_id' => $request->user()->id]);

        return redirect(route('shops.show', $request['shopId']));
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
    public function destroy(WorkSpace $workSpace)
    {
        //
    }
}
