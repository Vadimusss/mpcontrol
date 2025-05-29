<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'goodId' => 'required|integer',
            'viewId' => 'required|integer'
        ]);

        return Note::where([
            'date' => $request->date,
            'good_id' => $request->goodId,
            'view_id' => $request->viewId
        ])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function store(Request $request)
    {
        dump($request);
        $validated = $request->validate([
            'date' => 'required|date',
            'goodId' => 'required|integer',
            'viewId' => 'required|integer',
            'text' => 'required|string|max:1000'
        ]);

        return Note::create([
            'date' => $validated['date'],
            'good_id' => $validated['goodId'],
            'view_id' => $validated['viewId'],
            'user_id' => $request->user()->id,
            'text' => $validated['text']
        ]);
    }

    public function update(Request $request, Note $note)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:1000'
        ]);

        $note->update($validated);

        return $note;
    }

    public function destroy(Note $note)
    {
        $note->delete();

        return response()->noContent();
    }

    public function isNotesExists(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'goodId' => 'required|integer',
            'viewId' => 'required|integer',
        ]);

        return Note::where('date', $validated['date'])
            ->where('good_id', $validated['goodId'])
            ->where('view_id', $validated['viewId'])
            ->exists();
    }
}
