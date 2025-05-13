<?php

namespace App\Http\Controllers;

use App\Models\ViewState;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ViewStatesController extends Controller
{
    public function saveState(Request $request, $workspaceId, $viewId)
    {
        $validated = $request->validate([
            'viewState' => 'required|array',
        ]);
        
        ViewState::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'workspace_id' => $workspaceId,
                'view_id' => $viewId,
            ],
            ['view_state' => $validated['viewState']]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'View state saved'
        ]);
    }
}
