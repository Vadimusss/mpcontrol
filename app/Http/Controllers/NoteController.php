<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Good;
use App\Events\NoteUpdated;
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
        $validated = $request->validate([
            'date' => 'required|date',
            'goodId' => 'required|integer',
            'viewId' => 'required|integer',
            'text' => 'required|string|max:1000'
        ]);

        $note = Note::create([
            'date' => $validated['date'],
            'good_id' => $validated['goodId'],
            'view_id' => $validated['viewId'],
            'user_id' => $request->user()->id,
            'text' => $validated['text']
        ]);

        $good = Good::find($validated['goodId']);
        if ($good) {
            broadcast(new NoteUpdated(
                shopId: $good->shop_id,
                goodId: $validated['goodId'],
                date: $validated['date'],
                exists: true
            ));
        }

        return $note;
    }

    public function update(Request $request, Note $note)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:1000'
        ]);

        $note->update($validated);

        $good = $note->good;
        if ($good) {
            broadcast(new NoteUpdated(
                shopId: $good->shop_id,
                goodId: $note->good_id,
                date: $note->date->format('Y-m-d'),
                exists: true
            ));
        }

        return $note;
    }

    public function destroy(Note $note)
    {
        $good = $note->good;
        $goodId = $note->good_id;
        $date = $note->date->format('Y-m-d');
        
        $note->delete();

        if ($good) {
            broadcast(new NoteUpdated(
                shopId: $good->shop_id,
                goodId: $goodId,
                date: $date,
                exists: false
            ));
        }

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

    public function all(Request $request)
    {
        $request->validate([
            'view_id' => 'required|integer',
            'shop_id' => 'nullable|integer'
        ]);

        $query = Note::where('view_id', $request->view_id);

        if ($request->has('shop_id')) {
            $query->whereHas('good', function ($query) use ($request) {
                $query->where('shop_id', $request->shop_id);
            });
        }

        return $query->select(['good_id', 'date'])
            ->distinct()
            ->get()
            ->map(function ($note) {
                return [
                    'good_id' => $note->good_id,
                    'date' => $note->date->format('Y-m-d'),
                ];
            });
    }
}
